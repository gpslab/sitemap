<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream;

use GpsLab\Component\Sitemap\Sitemap\Sitemap;

interface SplitStream extends Stream
{
    /**
     * @return Sitemap[]|\Traversable
     */
    public function getSitemaps(): \Traversable;
}
