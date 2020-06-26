<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Exception;

class LocationTooLongException extends \DomainException
{
    /**
     * @param string $location
     * @param int    $max_length
     *
     * @return static
     */
    public static function longLocation($location, $max_length)
    {
        return new static(sprintf(
            'The location "%s" must be less than "%d" characters, got "%d" instead.',
            $location,
            $max_length,
            strlen($location)
        ));
    }
}
