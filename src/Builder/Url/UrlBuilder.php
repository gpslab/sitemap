<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Builder\Url;

use GpsLab\Component\Sitemap\Url\Url;

interface UrlBuilder extends \IteratorAggregate
{
    /**
     * @return Url[]|\Traversable
     */
    public function getIterator(): \Traversable;
}
