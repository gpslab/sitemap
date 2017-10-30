<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url;

class SmartUrl extends Url
{
    /**
     * @var array
     */
    private $change_freq_priority = [
        '1.0' => self::CHANGE_FREQ_HOURLY,
        '0.9' => self::CHANGE_FREQ_DAILY,
        '0.8' => self::CHANGE_FREQ_DAILY,
        '0.7' => self::CHANGE_FREQ_WEEKLY,
        '0.6' => self::CHANGE_FREQ_WEEKLY,
        '0.5' => self::CHANGE_FREQ_WEEKLY,
        '0.4' => self::CHANGE_FREQ_MONTHLY,
        '0.3' => self::CHANGE_FREQ_MONTHLY,
        '0.2' => self::CHANGE_FREQ_YEARLY,
        '0.1' => self::CHANGE_FREQ_YEARLY,
        '0.0' => self::CHANGE_FREQ_NEVER,
    ];

    /**
     * @param string                  $loc
     * @param \DateTimeImmutable|null $last_mod
     * @param string|null             $change_freq
     * @param string|null             $priority
     */
    public function __construct($loc, \DateTimeImmutable $last_mod = null, $change_freq = null, $priority = null)
    {
        // priority from loc
        if (!$priority) {
            $priority = $this->getPriorityFromLoc($loc);
        }

        // change freq from last mod
        if (!$change_freq && $last_mod instanceof \DateTimeImmutable) {
            $change_freq = $this->getChangeFreqFromLastMod($last_mod);
        }

        // change freq from priority
        if (!$change_freq) {
            $change_freq = $this->getChangeFreqFromPriority($priority);
        }

        parent::__construct($loc, $last_mod, $change_freq, $priority);
    }

    /**
     * @param string $loc
     *
     * @return string
     */
    private function getPriorityFromLoc($loc)
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

    /**
     * @param \DateTimeImmutable $last_mod
     *
     * @return string|null
     */
    private function getChangeFreqFromLastMod(\DateTimeImmutable $last_mod)
    {
        if ($last_mod < new \DateTimeImmutable('-1 year')) {
            return self::CHANGE_FREQ_YEARLY;
        }

        if ($last_mod < new \DateTimeImmutable('-1 month')) {
            return self::CHANGE_FREQ_MONTHLY;
        }

        return null;
    }

    /**
     * @param string $priority
     *
     * @return string|null
     */
    private function getChangeFreqFromPriority($priority)
    {
        if (isset($this->change_freq_priority[$priority])) {
            return $this->change_freq_priority[$priority];
        }

        return null;
    }
}
