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

final class ChangeFrequency
{
    public const ALWAYS = 'always';

    public const HOURLY = 'hourly';

    public const DAILY = 'daily';

    public const WEEKLY = 'weekly';

    public const MONTHLY = 'monthly';

    public const YEARLY = 'yearly';

    public const NEVER = 'never';

    public const AVAILABLE_CHANGE_FREQUENCY = [
        self::ALWAYS,
        self::HOURLY,
        self::DAILY,
        self::WEEKLY,
        self::MONTHLY,
        self::YEARLY,
        self::NEVER,
    ];

    private const CHANGE_FREQUENCY_PRIORITY = [
        0 => self::NEVER,
        1 => self::YEARLY,
        2 => self::YEARLY,
        3 => self::MONTHLY,
        4 => self::MONTHLY,
        5 => self::WEEKLY,
        6 => self::WEEKLY,
        7 => self::WEEKLY,
        8 => self::DAILY,
        9 => self::DAILY,
        10 => self::HOURLY,
    ];

    /**
     * @param string $change_frequency
     *
     * @return bool
     */
    public static function isValid(string $change_frequency): bool
    {
        return in_array($change_frequency, self::AVAILABLE_CHANGE_FREQUENCY, true);
    }

    /**
     * @param \DateTimeInterface $last_modify
     *
     * @return string|null
     */
    public static function getByLastModify(\DateTimeInterface $last_modify): ?string
    {
        $now = new \DateTimeImmutable();
        if ($last_modify < $now->modify('-1 year')) {
            return self::YEARLY;
        }

        if ($last_modify < $now->modify('-1 month')) {
            return self::MONTHLY;
        }

        if ($last_modify < $now->modify('-1 week')) {
            return self::WEEKLY;
        }

        return null;
    }

    /**
     * @param int $priority
     *
     * @return string|null
     */
    public static function getByPriority(int $priority): ?string
    {
        return self::CHANGE_FREQUENCY_PRIORITY[$priority] ?? null;
    }
}
