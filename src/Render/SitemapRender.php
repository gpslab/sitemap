<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Render;

use GpsLab\Component\Sitemap\Url\Url;

interface SitemapRender
{
    /**
     * @return string
     */
    public function start();

    /**
     * @return string
     */
    public function end();

    /**
     * @param Url $url
     *
     * @return string
     */
    public function url(Url $url);
}
