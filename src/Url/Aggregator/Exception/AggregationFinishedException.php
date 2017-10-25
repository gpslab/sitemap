<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Aggregator\Exception;

class AggregationFinishedException extends \RuntimeException
{
    /**
     * @return static
     */
    final public static function finished()
    {
        return new static('Aggregation of URLs is finished.');
    }
}
