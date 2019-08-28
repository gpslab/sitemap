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
     * @param string                  $location
     * @param \DateTimeInterface|null $last_modify
     *
     * @return string
     */
    public function sitemap(string $location, ?\DateTimeInterface $last_modify = null): string;
}
