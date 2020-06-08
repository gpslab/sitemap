<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Sitemap\Exception;

final class InvalidLocationException extends InvalidArgumentException
{
    /**
     * @param string $location
     *
     * @return InvalidLocationException
     */
    public static function invalid(string $location): self
    {
        return new self(sprintf('You specify "%s" the invalid path as the location.', $location));
    }
}
