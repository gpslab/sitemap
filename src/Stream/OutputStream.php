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

use GpsLab\Component\Sitemap\Render\SitemapRender;
use GpsLab\Component\Sitemap\Stream\Exception\LinksOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\SizeOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\State\StreamState;
use GpsLab\Component\Sitemap\Url\Url;

class OutputStream implements Stream
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
     * @var int
     */
    private $counter = 0;

    /**
     * @var int
     */
    private $used_bytes = 0;

    /**
     * @var string
     */
    private $end_string = '';

    /**
     * @var int
     */
    private $end_string_bytes = 0;

    /**
     * @param SitemapRender $render
     */
    public function __construct(SitemapRender $render)
    {
        $this->render = $render;
        $this->state = new StreamState();
    }

    public function open(): void
    {
        $this->state->open();

        $start_string = $this->render->start();
        $this->send($start_string);
        $this->used_bytes += mb_strlen($start_string, '8bit');
    }

    public function close(): void
    {
        $this->state->close();
        $this->send($this->end_string ?: $this->render->end());
        $this->counter = 0;
        $this->used_bytes = 0;
    }

    /**
     * @param Url $url
     */
    public function push(Url $url): void
    {
        if (!$this->state->isReady()) {
            throw StreamStateException::notReady();
        }

        if ($this->counter >= self::LINKS_LIMIT) {
            throw LinksOverflowException::withLimit(self::LINKS_LIMIT);
        }

        $render_url = $this->render->url($url);
        $write_bytes = mb_strlen($render_url, '8bit');

        // render end string after render first url
        if (!$this->end_string) {
            $this->end_string = $this->render->end();
            $this->end_string_bytes = mb_strlen($this->end_string, '8bit');
        }

        if ($this->used_bytes + $write_bytes + $this->end_string_bytes > self::BYTE_LIMIT) {
            throw SizeOverflowException::withLimit(self::BYTE_LIMIT);
        }

        $this->send($render_url);
        $this->used_bytes += $write_bytes;
        ++$this->counter;
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
