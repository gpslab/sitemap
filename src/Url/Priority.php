<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url;

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
     * @param string $location
     *
     * @return int
     */
    public static function getByLocation(string $location): int
    {
        // number of slashes
        $num = count(array_filter(explode('/', trim($location, '/'))));

        if (!$num) {
            return 10;
        }

        if (($p = (10 - $num) / 10) > 0) {
            return (int) ($p * 10);
        }

        return 1;
    }
}
