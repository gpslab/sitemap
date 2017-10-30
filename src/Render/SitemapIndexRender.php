<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Render;

interface SitemapIndexRender
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
     * @param string                  $url
     * @param \DateTimeImmutable|null $last_mod
     *
     * @return string
     */
    public function sitemap($url, \DateTimeImmutable $last_mod = null);
}
