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
     * @param string                  $loc
     * @param \DateTimeImmutable|null $last_mod
     * @param string|null             $change_freq
     * @param string|null             $priority
     */
    public function __construct($loc, \DateTimeImmutable $last_mod = null, $change_freq = null, $priority = null)
    {
        // priority from loc
        if (!$priority) {
            $priority = $this->priorityFromLoc($loc);
        }

        // change freq from last mod
        if (!$change_freq && $last_mod instanceof \DateTimeImmutable) {
            $change_freq = $this->changeFreqFromLastMod($last_mod);
        }

        // change freq from priority
        if (!$change_freq && $priority == '1.0') {
            $change_freq = self::CHANGE_FREQ_DAILY;
        }

        parent::__construct($loc, $last_mod, $change_freq, $priority);
    }

    /**
     * @param string $loc
     *
     * @return string
     */
    private function priorityFromLoc($loc)
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
    private function changeFreqFromLastMod(\DateTimeImmutable $last_mod)
    {
        if ($last_mod < new \DateTimeImmutable('-1 year')) {
            return self::CHANGE_FREQ_YEARLY;
        }

        if ($last_mod < new \DateTimeImmutable('-1 month')) {
            return self::CHANGE_FREQ_MONTHLY;
        }

        return null;
    }
}
