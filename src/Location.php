<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap;

final class Location
{
    /**
     * @param string $location
     *
     * @return bool
     */
    public static function isValid(string $location): bool
    {
        if (self::isLocal($location)) {
            return true;
        }

        return false !== filter_var(sprintf('https://example.com%s', $location), FILTER_VALIDATE_URL);
    }

    /**
     * @param string $location
     *
     * @return bool
     */
    public static function isLocal(string $location): bool
    {
        return !$location || in_array($location[0], ['/', '?', '#'], true);
    }
}
