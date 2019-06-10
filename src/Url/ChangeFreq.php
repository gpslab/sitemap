<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url;

final class ChangeFreq
{
    public const ALWAYS = 'always';

    public const HOURLY = 'hourly';

    public const DAILY = 'daily';

    public const WEEKLY = 'weekly';

    public const MONTHLY = 'monthly';

    public const YEARLY = 'yearly';

    public const NEVER = 'never';

    private const CHANGE_FREQ_PRIORITY = [
        '1.0' => self::HOURLY,
        '0.9' => self::DAILY,
        '0.8' => self::DAILY,
        '0.7' => self::WEEKLY,
        '0.6' => self::WEEKLY,
        '0.5' => self::WEEKLY,
        '0.4' => self::MONTHLY,
        '0.3' => self::MONTHLY,
        '0.2' => self::YEARLY,
        '0.1' => self::YEARLY,
        '0.0' => self::NEVER,
    ];

    /**
     * @param \DateTimeInterface $last_mod
     *
     * @return string|null
     */
    public static function getByLastMod(\DateTimeInterface $last_mod): ?string
    {
        if ($last_mod < new \DateTime('-1 year')) {
            return self::YEARLY;
        }

        if ($last_mod < new \DateTime('-1 month')) {
            return self::MONTHLY;
        }

        if ($last_mod < new \DateTime('-1 week')) {
            return self::WEEKLY;
        }

        return null;
    }

    /**
     * @param string $priority
     *
     * @return string|null
     */
    public static function getByPriority(string $priority): ?string
    {
        return self::CHANGE_FREQ_PRIORITY[$priority] ?? null;
    }
}
