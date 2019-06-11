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
     * @var string
     */
    private $host = '';

    /**
     * @param string $host
     */
    public function __construct(string $host)
    {
        $this->host = $host;
    }

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
     * @param string                  $path
     * @param \DateTimeInterface|null $last_mod
     *
     * @return string
     */
    public function sitemap(string $path, \DateTimeInterface $last_mod = null): string
    {
        return '<sitemap>'.
            '<loc>'.$this->host.$path.'</loc>'.
            ($last_mod ? sprintf('<lastmod>%s</lastmod>', $last_mod->format('c')) : '').
        '</sitemap>';
    }
}
