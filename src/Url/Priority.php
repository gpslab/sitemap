<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url;

final class Priority
{
    /**
     * @param float $priority
     *
     * @return bool
     */
    public static function isValid(float $priority): bool
    {
        return $priority >= 0 && $priority <= 1;
    }

    /**
     * @param string $location
     *
     * @return float
     */
    public static function getByLocation(string $location): float
    {
        // number of slashes
        $num = count(array_filter(explode('/', trim($location, '/'))));

        if (!$num) {
            return 1.0;
        }

        if (($p = (10 - $num) / 10) > 0) {
            return (float) $p;
        }

        return .1;
    }
}
