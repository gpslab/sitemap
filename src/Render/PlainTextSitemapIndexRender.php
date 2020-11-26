<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Render;

use GpsLab\Component\Sitemap\Sitemap\Sitemap;

final class PlainTextSitemapIndexRender implements SitemapIndexRender
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
     * @param Sitemap $sitemap
     *
     * @return string
     */
    public function sitemap(Sitemap $sitemap): string
    {
        $result = '<sitemap>';
        $result .= '<loc>'.htmlspecialchars((string) $sitemap->getLocation()).'</loc>';

        if ($sitemap->getLastModify()) {
            $result .= '<lastmod>'.$sitemap->getLastModify()->format('c').'</lastmod>';
        }

        $result .= '</sitemap>';

        return $result;
    }
}
