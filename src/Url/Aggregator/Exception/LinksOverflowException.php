<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Aggregator\Exception;

class LinksOverflowException extends OverflowException
{
    /**
     * @param int $links_limit
     *
     * @return static
     */
    final public static function withLimit($links_limit)
    {
        return new static(sprintf('The limit of %d URLs in the sitemap.xml was exceeded.', $links_limit));
    }
}
