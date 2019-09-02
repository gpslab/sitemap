<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream;

use GpsLab\Component\Sitemap\Limiter;
use GpsLab\Component\Sitemap\Render\SitemapRender;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\State\StreamState;
use GpsLab\Component\Sitemap\Url\Url;
use GpsLab\Component\Sitemap\Writer\Writer;

class WritingStream implements Stream
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
    private $filename;

    /**
     * @var string
     */
    private $end_string = '';

    /**
     * @param SitemapRender $render
     * @param Writer        $writer
     * @param string        $filename
     */
    public function __construct(SitemapRender $render, Writer $writer, string $filename)
    {
        $this->render = $render;
        $this->writer = $writer;
        $this->filename = $filename;
        $this->state = new StreamState();
        $this->limiter = new Limiter();
    }

    public function open(): void
    {
        $this->state->open();
        $start_string = $this->render->start();
        $this->end_string = $this->render->end();
        $this->writer->open($this->filename);
        $this->writer->write($start_string);
        $this->limiter->tryUseBytes(mb_strlen($start_string, '8bit'));
        $this->limiter->tryUseBytes(mb_strlen($this->end_string, '8bit'));
    }

    public function close(): void
    {
        $this->state->close();
        $this->writer->write($this->end_string);
        $this->writer->close();
        $this->limiter->reset();
    }

    /**
     * @param Url $url
     */
    public function push(Url $url): void
    {
        if (!$this->state->isReady()) {
            throw StreamStateException::notReady();
        }

        $this->limiter->tryAddUrl();
        $render_url = $this->render->url($url);
        $this->limiter->tryUseBytes(mb_strlen($render_url, '8bit'));
        $this->writer->write($render_url);
    }
}
