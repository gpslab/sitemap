<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Exception;

use GpsLab\Component\Sitemap\Exception\InvalidArgumentException;

final class LocationTooLongException extends InvalidArgumentException
{
    /**
     * @param string $location
     * @param int    $max_length
     *
     * @return self
     */
    public static function tooLong(string $location, int $max_length): self
    {
        return new static(sprintf(
            'The location "%s" must be less than "%d" characters, got "%d" instead.',
            $location,
            $max_length,
            strlen($location)
        ));
    }
}
