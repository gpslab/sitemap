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
use GpsLab\Component\Sitemap\Render\SitemapRender;
use GpsLab\Component\Sitemap\Sitemap\Sitemap;
use GpsLab\Component\Sitemap\Stream\Exception\OverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\SplitIndexException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\State\StreamState;
use GpsLab\Component\Sitemap\Url\Url;
use GpsLab\Component\Sitemap\Writer\Writer;

final class WritingSplitStream implements SplitStream
{
    /**
     * @var SitemapRender
     */
    private $render;

    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var StreamState
     */
    private $state;

    /**
     * @var Limiter
     */
    private $limiter;

    /**
     * @var string
     */
    private $filename_pattern;

    /**
     * @var string
     */
    private $web_path_pattern;

    /**
     * @var int
     */
    private $index = 1;

    /**
     * @var string
     */
    private $start_string = '';

    /**
     * @var string
     */
    private $end_string = '';

    /**
     * @var int[]
     */
    private $parts = [];

    /**
     * @param SitemapRender $render
     * @param Writer        $writer
     * @param string        $web_path_pattern
     * @param string        $filename_pattern
     *
     * @throws SplitIndexException
     */
    public function __construct(
        SitemapRender $render,
        Writer $writer,
        string $filename_pattern,
        string $web_path_pattern
    ) {
        if (
            sprintf($filename_pattern, $this->index) === $filename_pattern ||
            sprintf($filename_pattern, Limiter::SITEMAPS_LIMIT) === $filename_pattern
        ) {
            throw SplitIndexException::invalidPartFilenamePattern($filename_pattern);
        }

        if (
            sprintf($web_path_pattern, $this->index) === $web_path_pattern ||
            sprintf($web_path_pattern, Limiter::SITEMAPS_LIMIT) === $web_path_pattern ||
            filter_var(sprintf($web_path_pattern, $this->index), FILTER_VALIDATE_URL) === false
        ) {
            throw SplitIndexException::invalidPartWebPathPattern($web_path_pattern);
        }

        $this->filename_pattern = $filename_pattern;
        $this->web_path_pattern = $web_path_pattern;
        $this->render = $render;
        $this->writer = $writer;

        $this->state = new StreamState();
        $this->limiter = new Limiter();
    }

    /**
     * @throws StreamStateException
     */
    public function open(): void
    {
        $this->state->open();
        $this->openPart();
    }

    /**
     * @throws StreamStateException
     */
    public function close(): void
    {
        $this->state->close();

        $this->closePart();

        $this->index = 1;
        // free memory
        $this->start_string = '';
        $this->end_string = '';
        $this->parts = [];
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

            // create first part by first URL
            if (!$this->parts) {
                $this->parts[] = time();
            }
        } catch (OverflowException $e) {
            $this->closePart();
            // It would be better to take the read file modification time, but the writer may not create the file.
            // If the writer does not create the file, but the file already exists, then we may get the incorrect file
            // modification time. It will be better to use the current time. Time error will be negligible.
            $this->parts[] = time();

            ++$this->index;
            $this->openPart();
            $this->pushToPart($url);
        }
    }

    /**
     * @return Sitemap[]|\Traversable
     */
    public function getSitemaps(): \Traversable
    {
        foreach ($this->parts as $index => $modify_time) {
            yield new Sitemap(
                // indexes in the array start from zero, but the Sitemap partitions from one
                sprintf($this->web_path_pattern, $index + 1),
                (new \DateTimeImmutable())->setTimestamp($modify_time)
            );
        }
    }

    private function openPart(): void
    {
        $this->start_string = $this->start_string ?: $this->render->start();
        $this->end_string = $this->end_string ?: $this->render->end();
        $this->limiter->tryUseBytes(mb_strlen($this->start_string, '8bit'));
        $this->limiter->tryUseBytes(mb_strlen($this->end_string, '8bit'));
        $this->writer->start(sprintf($this->filename_pattern, $this->index));
        $this->writer->append($this->start_string);
    }

    private function closePart(): void
    {
        $this->writer->append($this->end_string);
        $this->writer->finish();
        $this->limiter->reset();
    }

    /**
     * @param Url $url
     */
    private function pushToPart(Url $url): void
    {
        $this->limiter->tryAddUrl();
        $render_url = $this->render->url($url);
        $this->limiter->tryUseBytes(mb_strlen($render_url, '8bit'));
        $this->writer->append($render_url);
    }
}
