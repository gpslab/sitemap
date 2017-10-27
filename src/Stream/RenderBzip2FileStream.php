<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream;

use GpsLab\Component\Sitemap\Render\SitemapRender;
use GpsLab\Component\Sitemap\Stream\Exception\FileAccessException;
use GpsLab\Component\Sitemap\Stream\Exception\LinksOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\SizeOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\State\StreamState;
use GpsLab\Component\Sitemap\Url\Url;

class RenderBzip2FileStream implements FileStream
{
    const LINKS_LIMIT = 50000;

    const BYTE_LIMIT = 52428800; // 50 Mb

    /**
     * @var SitemapRender
     */
    private $render;

    /**
     * @var StreamState
     */
    private $state;

    /**
     * @var resource|null
     */
    private $handle;

    /**
     * @var string
     */
    private $filename = '';

    /**
     * @var int
     */
    private $counter = 0;

    /**
     * @var string
     */
    private $end_string = '';

    /**
     * @param SitemapRender $render
     * @param string        $filename
     */
    public function __construct(SitemapRender $render, $filename)
    {
        $this->render = $render;
        $this->state = new StreamState();
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    public function open()
    {
        $this->state->open();

        if (!is_writable($this->filename) || ($this->handle = @bzopen($this->filename, 'w')) === false) {
            throw FileAccessException::notWritable($this->filename);
        }

        $this->write($this->render->start());
        // render end string only once
        $this->end_string = $this->render->end();
    }

    public function close()
    {
        $this->state->close();
        $this->write($this->end_string);
        bzclose($this->handle);
    }

    /**
     * @param Url $url
     */
    public function push(Url $url)
    {
        if (!$this->state->isReady()) {
            throw StreamStateException::notReady();
        }

        if ($this->counter >= self::LINKS_LIMIT) {
            throw LinksOverflowException::withLimit(self::LINKS_LIMIT);
        }

        $render_url = $this->render->url($url);

        $expected_bytes = filesize($this->filename) + strlen($render_url) + strlen($this->end_string);
        if ($expected_bytes > self::BYTE_LIMIT) {
            throw SizeOverflowException::withLimit(self::BYTE_LIMIT);
        }

        $this->write($render_url);
        ++$this->counter;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->counter;
    }

    /**
     * @param string $string
     */
    private function write($string)
    {
        if (fwrite($this->handle, $string) === false) {
            throw FileAccessException::failedWrite($this->filename, $string);
        }
    }
}
