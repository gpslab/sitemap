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
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\State\StreamState;
use GpsLab\Component\Sitemap\Url\Url;

class RenderBzip2FileStream implements FileStream
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

        if ((file_exists($this->filename) && !is_writable($this->filename)) ||
            ($this->handle = @bzopen($this->filename, 'w')) === false
        ) {
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
        $this->counter = 0;
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
        bzwrite($this->handle, $string);
    }
}
