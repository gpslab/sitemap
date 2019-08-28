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

class PlainTextSitemapIndexRender implements SitemapIndexRender
{
    /**
     * @return string
     */
    public function start(): string
    {
        return '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    }

    /**
     * @return string
     */
    public function end(): string
    {
        return '</sitemapindex>'.PHP_EOL;
    }

    /**
     * @param string                  $location
     * @param \DateTimeInterface|null $last_modify
     *
     * @return string
     */
    public function sitemap(string $location, \DateTimeInterface $last_modify = null): string
    {
        return '<sitemap>'.
            '<loc>'.$location.'</loc>'.
            ($last_modify ? sprintf('<lastmod>%s</lastmod>', $last_modify->format('c')) : '').
        '</sitemap>';
    }
}
