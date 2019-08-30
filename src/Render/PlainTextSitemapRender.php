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
     * @var string
     */
    private $web_path;

    /**
     * @var bool
     */
    private $validating;

    /**
     * @param string $web_path
     * @param bool   $validating
     */
    public function __construct(string $web_path, bool $validating = true)
    {
        $this->web_path = $web_path;
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
        $result = '<url>';
        $result .= '<loc>'.htmlspecialchars($this->web_path.$url->getLocation()).'</loc>';

        if ($url->getLastModify() instanceof \DateTimeInterface) {
            $result .= '<lastmod>'.$url->getLastModify()->format('c').'</lastmod>';
        }
        if ($url->getChangeFrequency() !== null) {
            $result .= '<changefreq>'.$url->getChangeFrequency().'</changefreq>';
        }
        if ($url->getPriority() !== null) {
            $result .= '<priority>'.number_format($url->getPriority() / 10, 1).'</priority>';
        }

        $result .= '</url>';

        return $result;
    }
}
