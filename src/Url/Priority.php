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
    public const P10 = '1.0';

    public const P9 = '0.9';

    public const P8 = '0.8';

    public const P7 = '0.7';

    public const P6 = '0.6';

    public const P5 = '0.5';

    public const P4 = '0.4';

    public const P3 = '0.3';

    public const P2 = '0.2';

    public const P1 = '0.1';

    public const P0 = '0.0';

    /**
     * @param string $loc
     *
     * @return string
     */
    public static function getByLoc(string $loc): string
    {
        // number of slashes
        $num = count(array_filter(explode('/', trim($loc, '/'))));

        if (!$num) {
            return '1.0';
        }

        if (($p = (10 - $num) / 10) > 0) {
            return '0.'.($p * 10);
        }

        return '0.1';
    }
}
