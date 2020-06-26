<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url;

use GpsLab\Component\Sitemap\Url\Exception\LocationTooLongException;

class Url
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
     * The location must be less than 2048 characters.
     */
    const LOCATION_MAX_LENGTH = 2047;

    /**
     * @var string
     */
    private $loc;

    /**
     * @var \DateTimeImmutable
     */
    private $last_mod;

    /**
     * @var string
     */
    private $change_freq;

    /**
     * @var string
     */
    private $priority;

    /**
     * @param string                  $loc
     * @param \DateTimeImmutable|null $last_mod
     * @param string|null             $change_freq
     * @param string|null             $priority
     */
    public function __construct($loc, \DateTimeImmutable $last_mod = null, $change_freq = null, $priority = null)
    {
        if (strlen($loc) > self::LOCATION_MAX_LENGTH) {
            throw LocationTooLongException::longLocation($loc, self::LOCATION_MAX_LENGTH);
        }

        $this->loc = $loc;
        $this->last_mod = $last_mod ?: new \DateTimeImmutable();
        $this->change_freq = $change_freq ?: self::DEFAULT_CHANGE_FREQ;
        $this->priority = $priority ?: self::DEFAULT_PRIORITY;
    }

    /**
     * @return string
     */
    public function getLoc()
    {
        return $this->loc;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getLastMod()
    {
        return $this->last_mod;
    }

    /**
     * @return string
     */
    public function getChangeFreq()
    {
        return $this->change_freq;
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }
}
