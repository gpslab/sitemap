<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Builder\Url;

use GpsLab\Component\Sitemap\Url\Url;

interface UrlBuilder extends \Countable, \IteratorAggregate
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return Url[]
     */
    public function getIterator();
}
