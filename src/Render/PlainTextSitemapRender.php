<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Render;

use GpsLab\Component\Sitemap\Url\Url;

final class PlainTextSitemapRender implements SitemapRender
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
                ' xmlns:xhtml="http://www.w3.org/1999/xhtml"'.
                '>';
        }

        return '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
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
        $result = '<url>';
        $result .= '<loc>'.htmlspecialchars((string) $url->getLocation()).'</loc>';

        if ($url->getLastModify() instanceof \DateTimeInterface) {
            $result .= '<lastmod>'.$url->getLastModify()->format('c').'</lastmod>';
        }

        if ($url->getChangeFrequency()) {
            $result .= '<changefreq>'.$url->getChangeFrequency().'</changefreq>';
        }

        if ($url->getPriority() !== null) {
            $result .= '<priority>'.$url->getPriority().'</priority>';
        }

        foreach ($url->getLanguages() as $language) {
            $location = htmlspecialchars((string) $language->getLocation());
            $result .= '<xhtml:link rel="alternate" hreflang="'.$language->getLanguage().'" href="'.$location.'"/>';
        }

        $result .= '</url>';

        return $result;
    }
}
