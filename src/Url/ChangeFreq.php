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
        '1.0' => ChangeFreq::HOURLY,
        '0.9' => ChangeFreq::DAILY,
        '0.8' => ChangeFreq::DAILY,
        '0.7' => ChangeFreq::WEEKLY,
        '0.6' => ChangeFreq::WEEKLY,
        '0.5' => ChangeFreq::WEEKLY,
        '0.4' => ChangeFreq::MONTHLY,
        '0.3' => ChangeFreq::MONTHLY,
        '0.2' => ChangeFreq::YEARLY,
        '0.1' => ChangeFreq::YEARLY,
        '0.0' => ChangeFreq::NEVER,
    ];

    /**
     * @param \DateTimeImmutable $last_mod
     *
     * @return string|null
     */
    public static function getByLastMod(\DateTimeImmutable $last_mod): ?string
    {
        if ($last_mod < new \DateTimeImmutable('-1 year')) {
            return ChangeFreq::YEARLY;
        }

        if ($last_mod < new \DateTimeImmutable('-1 month')) {
            return ChangeFreq::MONTHLY;
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
