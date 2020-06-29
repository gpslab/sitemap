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
use GpsLab\Component\Sitemap\Sitemap\Sitemap;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\State\StreamState;
use GpsLab\Component\Sitemap\Writer\Writer;

final class WritingIndexStream implements IndexStream
{
    /**
     * @var SitemapIndexRender
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
    private $filename;

    /**
     * @param SitemapIndexRender $render
     * @param Writer             $writer
     * @param string             $filename
     */
    public function __construct(SitemapIndexRender $render, Writer $writer, string $filename)
    {
        $this->render = $render;
        $this->writer = $writer;
        $this->filename = $filename;
        $this->state = new StreamState();
        $this->limiter = new Limiter();
    }

    /**
     * @throws StreamStateException
     */
    public function open(): void
    {
        $this->state->open();
        $this->writer->start($this->filename);
        $this->writer->append($this->render->start());
    }

    /**
     * @throws StreamStateException
     */
    public function close(): void
    {
        $this->state->close();
        $this->writer->append($this->render->end());
        $this->writer->finish();
        $this->limiter->reset();
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

        $this->limiter->tryAddSitemap();
        $this->writer->append($this->render->sitemap($sitemap));
    }
}
