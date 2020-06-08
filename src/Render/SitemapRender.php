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

interface SitemapRender
{
    /**
     * @return string
     */
    public function start(): string;

    /**
     * @return string
     */
    public function end(): string;

    /**
     * @param Url $url
     *
     * @return string
     */
    public function url(Url $url): string;
}
