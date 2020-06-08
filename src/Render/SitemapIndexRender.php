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

interface SitemapIndexRender
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
     * @param Sitemap $sitemap
     *
     * @return string
     */
    public function sitemap(Sitemap $sitemap): string;
}
