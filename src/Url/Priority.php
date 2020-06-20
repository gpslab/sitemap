<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url;

use GpsLab\Component\Sitemap\Location;

final class Priority
{
    /**
     * @param int $priority
     *
     * @return bool
     */
    public static function isValid(int $priority): bool
    {
        return $priority >= 0 && $priority <= 10;
    }

    /**
     * @param Location $location
     *
     * @return int
     */
    public static function getByLocation(Location $location): int
    {
        // number of slashes
        $num = count(array_filter(explode('/', trim((string) $location, '/'))));

        if (!$num) {
            return 10;
        }

        if (($p = (10 - $num) / 10) > 0) {
            return (int) ($p * 10);
        }

        return 1;
    }
}
