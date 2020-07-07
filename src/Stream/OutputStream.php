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
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\State\StreamState;
use GpsLab\Component\Sitemap\Url\Url;

final class OutputStream implements Stream
{
    /**
     * @var SitemapRender
     */
    private $render;

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
    private $end_string = '';

    /**
     * @param SitemapRender $render
     */
    public function __construct(SitemapRender $render)
    {
        $this->render = $render;
        $this->state = new StreamState();
        $this->limiter = new Limiter();
    }

    /**
     * @throws StreamStateException
     */
    public function open(): void
    {
        $this->state->open();
        $start_string = $this->render->start();
        $this->end_string = $this->render->end();
        $this->send($start_string);
        $this->limiter->tryUseBytes(mb_strlen($start_string, '8bit'));
        $this->limiter->tryUseBytes(mb_strlen($this->end_string, '8bit'));
    }

    /**
     * @throws StreamStateException
     */
    public function close(): void
    {
        $this->state->close();
        $this->send($this->end_string);
        $this->limiter->reset();
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

        $this->limiter->tryAddUrl();
        $render_url = $this->render->url($url);
        $this->limiter->tryUseBytes(mb_strlen($render_url, '8bit'));
        $this->send($render_url);
    }

    /**
     * @param string $content
     */
    private function send(string $content): void
    {
        echo $content;
        flush();
    }
}
