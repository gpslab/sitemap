<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Aggregator;

use GpsLab\Component\Sitemap\Render\SitemapRender;
use GpsLab\Component\Sitemap\Url\Aggregator\Exception\AggregationFinishedException;
use GpsLab\Component\Sitemap\Url\Aggregator\Exception\LinksOverflowException;
use GpsLab\Component\Sitemap\Url\Aggregator\Exception\SizeOverflowException;
use GpsLab\Component\Sitemap\Url\Url;

class FileUrlAggregator implements UrlAggregator
{
    const LINKS_LIMIT = 50000;

    const BYTE_LIMIT = 52428800; // 50 Mb

    /**
     * @var SitemapRender
     */
    private $render;

    /**
     * @var \SplFileObject
     */
    private $file;

    /**
     * @var int
     */
    private $counter = 0;

    /**
     * Aggregation finished.
     *
     * @var bool
     */
    private $finished = false;

    /**
     * @param SitemapRender $render
     * @param string        $filename
     */
    public function __construct(SitemapRender $render, $filename)
    {
        $this->render = $render;
        $this->file = new \SplFileObject($filename, 'wb');
        $this->file->fwrite($this->render->start());
    }

    /**
     * @param Url $url
     */
    public function add(Url $url)
    {
        if ($this->finished) {
            throw AggregationFinishedException::finished();
        }

        if ($this->counter >= self::LINKS_LIMIT) {
            throw LinksOverflowException::withLimit(self::LINKS_LIMIT);
        }

        if ($this->file->getSize() >= self::BYTE_LIMIT) {
            throw SizeOverflowException::withLimit(self::BYTE_LIMIT);
        }

        $render_url = $this->render->url($url);

        $expected_size = $this->file->getSize() + strlen($render_url) + strlen($this->render->end());
        if ($expected_size > self::BYTE_LIMIT) {
            throw SizeOverflowException::withLimit(self::BYTE_LIMIT);
        }

        $this->file->fwrite($render_url);
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
     * Always finish URL aggregation.
     */
    public function finish()
    {
        if ($this->finished) {
            throw AggregationFinishedException::finished();
        }

        $this->file->fwrite($this->render->end());
        $this->finished = true;
    }

    public function __destruct()
    {
        if (!$this->finished) {
            $this->finish();
        }
    }
}
