<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Render;

use GpsLab\Component\Sitemap\Url\Url;

class PlainTextSitemapRender implements SitemapRender
{
    /**
     * @var bool
     */
    private $validating;

    /**
     * @param bool $validating
     */
    public function __construct(bool $validating = true)
    {
        $this->validating = $validating;
    }

    /**
     * @return string
     */
    public function start(): string
    {
        if ($this->validating) {
            return '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.
                '<urlset'.
                ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'.
                ' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9'.
                ' http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"'.
                ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.
                '>';
        }

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
            '<loc>'.htmlspecialchars($url->getLocation()).'</loc>'.
            '<lastmod>'.$url->getLastModify()->format('c').'</lastmod>'.
            '<changefreq>'.$url->getChangeFreq().'</changefreq>'.
            '<priority>'.$url->getPriority().'</priority>'.
        '</url>';
    }
}
