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
    private $host;

    /**
     * @var bool
     */
    private $validating;

    /**
     * @param string $host
     * @param bool   $validating
     */
    public function __construct(string $host, bool $validating = true)
    {
        $this->host = $host;
        $this->validating = $validating;
    }

    /**
     * @return string
     */
    public function start(): string
    {
        if ($this->validating) {
            return '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.
                '<sitemapindex'.
                ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'.
                ' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9'.
                ' http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd"'.
                ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.
                '>';
        }

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
     * @param \DateTimeInterface|null $last_modify
     *
     * @return string
     */
    public function sitemap(string $path, \DateTimeInterface $last_modify = null): string
    {
        return '<sitemap>'.
            '<loc>'.$this->host.$path.'</loc>'.
            ($last_modify ? sprintf('<lastmod>%s</lastmod>', $last_modify->format('c')) : '').
        '</sitemap>';
    }
}
