<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Uri;

interface Url
{
    const CHANGE_FREQ_ALWAYS = 'always';
    const CHANGE_FREQ_HOURLY = 'hourly';
    const CHANGE_FREQ_DAILY = 'daily';
    const CHANGE_FREQ_WEEKLY = 'weekly';
    const CHANGE_FREQ_MONTHLY = 'monthly';
    const CHANGE_FREQ_YEARLY = 'yearly';
    const CHANGE_FREQ_NEVER = 'never';

    const DEFAULT_PRIORITY = '1.0';

    const DEFAULT_CHANGE_FREQ = self::CHANGE_FREQ_WEEKLY;

    /**
     * @param string $loc
     */
    public function __construct($loc);

    /**
     * @return string
     */
    public function getLoc();

    /**
     * @return \DateTimeImmutable
     */
    public function getLastMod();

    /**
     * @return string
     */
    public function getChangeFreq();

    /**
     * @return string
     */
    public function getPriority();
}
