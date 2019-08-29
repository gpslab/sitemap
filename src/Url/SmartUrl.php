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

class SmartUrl extends Url
{
    /**
     * @param string                  $location
     * @param \DateTimeInterface|null $last_modify
     * @param string|null             $change_freq
     * @param string|null             $priority
     */
    public function __construct(
        string $location,
        ?\DateTimeInterface $last_modify = null,
        ?string $change_freq = null,
        ?string $priority = null
    ) {
        // priority from loc
        if ($priority === null) {
            $priority = Priority::getByLocation($location);
        }

        // change freq from last mod
        if ($change_freq === null && $last_modify instanceof \DateTimeInterface) {
            $change_freq = ChangeFreq::getByLastModify($last_modify);
        }

        // change freq from priority
        if ($change_freq === null) {
            $change_freq = ChangeFreq::getByPriority($priority);
        }

        parent::__construct($location, $last_modify, $change_freq, $priority);
    }
}
