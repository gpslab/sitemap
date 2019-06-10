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

class SmartUrl extends Url
{
    /**
     * @param string                  $loc
     * @param \DateTimeImmutable|null $last_mod
     * @param string|null             $change_freq
     * @param string|null             $priority
     */
    public function __construct(
        string $loc,
        ?\DateTimeImmutable $last_mod = null,
        ?string $change_freq = null,
        ?string $priority = null
    ) {
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
    private function getPriorityFromLoc(string $loc): string
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
    private function getChangeFreqFromLastMod(\DateTimeImmutable $last_mod): ?string
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
    private function getChangeFreqFromPriority(string $priority): ?string
    {
        $change_freq_priority = [
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

        if (isset($change_freq_priority[$priority])) {
            return $change_freq_priority[$priority];
        }

        return null;
    }
}
