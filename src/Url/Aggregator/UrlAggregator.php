<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Aggregator;

use GpsLab\Component\Sitemap\Url\Url;

interface UrlAggregator extends \Countable
{
    /**
     * @param Url $url
     */
    public function add(Url $url);
}
