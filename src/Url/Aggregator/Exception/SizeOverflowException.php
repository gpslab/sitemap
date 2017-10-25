<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Aggregator\Exception;

class SizeOverflowException extends OverflowException
{
    /**
     * @param int $byte_limit
     *
     * @return static
     */
    final public static function withLimit($byte_limit)
    {
        return new static(sprintf('The limit of %d byte in the sitemap.xml was exceeded.', $byte_limit));
    }
}
