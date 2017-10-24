<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Render;

class PlainTextSitemapIndexRender implements SitemapIndexRender
{
    /**
     * @var string
     */
    private $host = '';

    /**
     * @param string $host
     */
    public function __construct($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function start()
    {
        return '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    }

    /**
     * @return string
     */
    public function end()
    {
        return '</sitemapindex>'.PHP_EOL;
    }

    /**
     * @param string                  $filename
     * @param \DateTimeImmutable|null $last_mod
     *
     * @return string
     */
    public function sitemap($filename, \DateTimeImmutable $last_mod = null)
    {
        return '<sitemap>'.
            sprintf('<loc>%s%s</loc>', $this->host, $filename).
            ($last_mod ? sprintf('<lastmod>%s</lastmod>', $last_mod->format('c')) : '').
        '</sitemap>';
    }
}
