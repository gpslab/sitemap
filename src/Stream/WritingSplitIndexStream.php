<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream;

use GpsLab\Component\Sitemap\Limiter;
use GpsLab\Component\Sitemap\Render\SitemapIndexRender;
use GpsLab\Component\Sitemap\Render\SitemapRender;
use GpsLab\Component\Sitemap\Sitemap\Sitemap;
use GpsLab\Component\Sitemap\Stream\Exception\OverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\SplitIndexException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\State\StreamState;
use GpsLab\Component\Sitemap\Url\Url;
use GpsLab\Component\Sitemap\Writer\Writer;

final class WritingSplitIndexStream implements Stream, IndexStream
{
    /**
     * @var SitemapIndexRender
     */
    private $index_render;

    /**
     * @var SitemapRender
     */
    private $part_render;

    /**
     * @var Writer
     */
    private $index_writer;

    /**
     * @var Writer
     */
    private $part_writer;

    /**
     * @var StreamState
     */
    private $state;

    /**
     * @var Limiter
     */
    private $index_limiter;

    /**
     * @var Limiter
     */
    private $part_limiter;

    /**
     * @var string
     */
    private $index_filename;

    /**
     * @var string
     */
    private $part_filename_pattern;

    /**
     * @var string
     */
    private $part_web_path_pattern;

    /**
     * @var int
     */
    private $index = 1;

    /**
     * @var bool
     */
    private $empty_index_part = true;

    /**
     * @var string
     */
    private $part_start_string = '';

    /**
     * @var string
     */
    private $part_end_string = '';

    /**
     * @param SitemapIndexRender $index_render
     * @param SitemapRender      $part_render
     * @param Writer             $index_writer
     * @param Writer             $part_writer
     * @param string             $index_filename
     * @param string             $part_filename_pattern
     * @param string             $part_web_path_pattern
     *
     * @throws SplitIndexException
     */
    public function __construct(
        SitemapIndexRender $index_render,
        SitemapRender $part_render,
        Writer $index_writer,
        Writer $part_writer,
        string $index_filename,
        string $part_filename_pattern,
        string $part_web_path_pattern
    ) {
        // conflict warning
        if ($index_writer === $part_writer) {
            @trigger_error(
                'It\'s better not to use one writer as a part writer and a index writer.'.
                ' This can cause conflicts in the writer.',
                E_USER_WARNING
            );
        }

        if (
            sprintf($part_filename_pattern, $this->index) === $part_filename_pattern ||
            sprintf($part_filename_pattern, Limiter::SITEMAPS_LIMIT) === $part_filename_pattern
        ) {
            throw SplitIndexException::invalidPartFilenamePattern($part_filename_pattern);
        } else {
            $this->part_filename_pattern = $part_filename_pattern;
        }

        if (
            sprintf($part_web_path_pattern, $this->index) === $part_web_path_pattern ||
            sprintf($part_web_path_pattern, Limiter::SITEMAPS_LIMIT) === $part_web_path_pattern ||
            filter_var(sprintf($part_web_path_pattern, $this->index), FILTER_VALIDATE_URL) === false
        ) {
            throw SplitIndexException::invalidPartWebPathPattern($part_web_path_pattern);
        }

        $this->part_web_path_pattern = $part_web_path_pattern;

        $this->index_render = $index_render;
        $this->part_render = $part_render;
        $this->index_writer = $index_writer;
        $this->part_writer = $part_writer;
        $this->index_filename = $index_filename;

        $this->state = new StreamState();
        $this->index_limiter = new Limiter();
        $this->part_limiter = new Limiter();
    }

    /**
     * @throws StreamStateException
     */
    public function open(): void
    {
        $this->state->open();
        $this->openPart();
        $this->index_writer->start($this->index_filename);
        $this->index_writer->append($this->index_render->start());
    }

    /**
     * @throws StreamStateException
     */
    public function close(): void
    {
        $this->state->close();

        $this->closePart();

        // not add empty sitemap part to index
        if (!$this->empty_index_part) {
            $this->addIndexPartToIndex($this->index);
        }

        $this->index_writer->append($this->index_render->end());
        $this->index_writer->finish();
        $this->index_limiter->reset();

        $this->index = 1;
        // free memory
        $this->part_start_string = '';
        $this->part_end_string = '';
    }

    /**
     * @param Url $url
     *
     * @throws StreamStateException
     */
    public function push(Url $url): void
    {
        if (!$this->state->isReady()) {
            throw StreamStateException::notReady();
        }

        try {
            $this->pushToPart($url);
        } catch (OverflowException $e) {
            $this->closePart();
            $this->addIndexPartToIndex($this->index);
            ++$this->index;
            $this->openPart();
            $this->pushToPart($url);
        }

        $this->empty_index_part = false;
    }

    /**
     * @param Sitemap $sitemap
     *
     * @throws StreamStateException
     */
    public function pushSitemap(Sitemap $sitemap): void
    {
        if (!$this->state->isReady()) {
            throw StreamStateException::notReady();
        }

        $this->index_limiter->tryAddSitemap();
        $this->index_writer->append($this->index_render->sitemap($sitemap));
    }

    private function openPart(): void
    {
        $this->part_start_string = $this->part_start_string ?: $this->part_render->start();
        $this->part_end_string = $this->part_end_string ?: $this->part_render->end();
        $this->part_limiter->tryUseBytes(mb_strlen($this->part_start_string, '8bit'));
        $this->part_limiter->tryUseBytes(mb_strlen($this->part_end_string, '8bit'));
        $this->part_writer->start(sprintf($this->part_filename_pattern, $this->index));
        $this->part_writer->append($this->part_start_string);
    }

    private function closePart(): void
    {
        $this->part_writer->append($this->part_end_string);
        $this->part_writer->finish();
        $this->part_limiter->reset();
    }

    /**
     * @param Url $url
     */
    private function pushToPart(Url $url): void
    {
        $this->part_limiter->tryAddUrl();
        $render_url = $this->part_render->url($url);
        $this->part_limiter->tryUseBytes(mb_strlen($render_url, '8bit'));
        $this->part_writer->append($render_url);
    }

    /**
     * @param int $index
     */
    private function addIndexPartToIndex(int $index): void
    {
        $this->index_limiter->tryAddSitemap();
        // It would be better to take the read file modification time, but the writer may not create the file.
        // If the writer does not create the file, but the file already exists, then we may get the incorrect file
        // modification time. It will be better to use the current time. Time error will be negligible.
        $this->index_writer->append($this->index_render->sitemap(new Sitemap(
            sprintf($this->part_web_path_pattern, $index),
            new \DateTimeImmutable()
        )));
    }
}
