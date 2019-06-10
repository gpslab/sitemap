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
            $priority = Priority::getByLoc($loc);
        }

        // change freq from last mod
        if (!$change_freq && $last_mod instanceof \DateTimeImmutable) {
            $change_freq = ChangeFreq::getByLastMod($last_mod);
        }

        // change freq from priority
        if (!$change_freq) {
            $change_freq = ChangeFreq::getByPriority($priority);
        }

        parent::__construct($loc, $last_mod, $change_freq, $priority);
    }
}
