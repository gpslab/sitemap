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
use GpsLab\Component\Sitemap\Stream\Exception\CompressionLevelException;
use GpsLab\Component\Sitemap\Stream\Exception\FileAccessException;
use GpsLab\Component\Sitemap\Stream\Exception\LinksOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\State\StreamState;
use GpsLab\Component\Sitemap\Url\Url;

class RenderGzipFileStream implements FileStream
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
    private $compression_level = 9;

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
     * @param int           $compression_level
     */
    public function __construct(SitemapRender $render, string $filename, int $compression_level = 9)
    {
        if ($compression_level < 1 || $compression_level > 9) {
            throw CompressionLevelException::invalid($compression_level, 1, 9);
        }

        $this->render = $render;
        $this->state = new StreamState();
        $this->filename = $filename;
        $this->compression_level = $compression_level;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    public function open(): void
    {
        $this->state->open();

        $mode = 'wb'.$this->compression_level;
        if ((file_exists($this->filename) && !is_writable($this->filename)) ||
            ($this->handle = @gzopen($this->filename, $mode)) === false
        ) {
            throw FileAccessException::notWritable($this->filename);
        }

        $this->write($this->render->start());
        // render end string only once
        $this->end_string = $this->render->end();
    }

    public function close(): void
    {
        $this->state->close();
        $this->write($this->end_string);
        gzclose($this->handle);
        $this->counter = 0;
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

        $this->write($render_url);
        ++$this->counter;
    }

    /**
     * @param string $string
     */
    private function write(string $string): void
    {
        gzwrite($this->handle, $string);
    }
}
