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

class CallbackStream implements Stream
{
    /**
     * @var SitemapRender
     */
    private $render;

    /**
     * @var callable
     */
    private $callback;

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
     * @param SitemapRender $render
     * @param callable      $callback
     */
    public function __construct(SitemapRender $render, callable $callback)
    {
        $this->render = $render;
        $this->callback = $callback;
        $this->state = new StreamState();
    }

    public function open(): void
    {
        $this->state->open();
        $this->send($this->render->start());
        // render end string only once
        $this->end_string = $this->render->end();
    }

    public function close(): void
    {
        $this->state->close();
        $this->send($this->end_string);
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
        $expected_bytes = $this->used_bytes + mb_strlen($render_url, '8bit') + mb_strlen($this->end_string, '8bit');

        if ($expected_bytes > self::BYTE_LIMIT) {
            throw SizeOverflowException::withLimit(self::BYTE_LIMIT);
        }

        $this->send($render_url);
        ++$this->counter;
    }

    /**
     * @param string $content
     */
    private function send(string $content): void
    {
        call_user_func($this->callback, $content);
        $this->used_bytes += mb_strlen($content, '8bit');
    }
}
