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

class RenderFileStream implements FileStream
{
    const LINKS_LIMIT = 50000;

    const BYTE_LIMIT = 52428800; // 50 Mb

    /**
     * @var SitemapRender
     */
    private $render;

    /**
     * @var \SplFileObject|null
     */
    private $file;

    /**
     * @var StreamState
     */
    private $state;

    /**
     * @var string
     */
    private $filename = '';

    /**
     * @var int
     */
    private $counter = 0;

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
        $this->file = new \SplFileObject($this->filename, 'wb');

        if (!$this->file->isWritable()) {
            throw FileAccessException::notWritable($this->filename);
        }

        $this->write($this->render->start());
    }

    public function close()
    {
        $this->state->close();
        $this->write($this->render->end());
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

        if ($this->file->getSize() >= self::BYTE_LIMIT) {
            throw SizeOverflowException::withLimit(self::BYTE_LIMIT);
        }

        $render_url = $this->render->url($url);

        $expected_bytes = $this->file->getSize() + strlen($render_url) + strlen($this->render->end());
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
        if ($this->file->fwrite($string) === 0) {
            throw FileAccessException::failedWrite($this->filename ,$string);
        }
    }
}
