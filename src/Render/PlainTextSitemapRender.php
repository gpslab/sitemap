<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Render;

use GpsLab\Component\Sitemap\Url\Url;

class PlainTextSitemapRender implements SitemapRender
{
    /**
     * @return string
     */
    public function start(): string
    {
        return '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    }

    /**
     * @return string
     */
    public function end(): string
    {
        return '</urlset>'.PHP_EOL;
    }

    /**
     * @param Url $url
     *
     * @return string
     */
    public function url(Url $url): string
    {
        return '<url>'.
            '<loc>'.htmlspecialchars($url->getLoc()).'</loc>'.
            '<lastmod>'.$url->getLastMod()->format('Y-m-d').'</lastmod>'.
            '<changefreq>'.$url->getChangeFreq().'</changefreq>'.
            '<priority>'.$url->getPriority().'</priority>'.
        '</url>';
    }
}
