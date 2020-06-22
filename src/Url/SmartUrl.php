<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url;

use GpsLab\Component\Sitemap\Location;

class SmartUrl extends Url
{
    /**
     * @param Location|string                $location
     * @param \DateTimeInterface|null        $last_modify
     * @param ChangeFrequency|string|null    $change_frequency
     * @param Priority|string|float|int|null $priority
     * @param array<string, string>          $languages
     */
    public function __construct(
        $location,
        ?\DateTimeInterface $last_modify = null,
        $change_frequency = null,
        $priority = null,
        array $languages = []
    ) {
        $location = $location instanceof Location ? $location : new Location($location);

        // priority from loc
        if ($priority === null) {
            $priority = Priority::createByLocation($location);
        } elseif (!$priority instanceof Priority) {
            $priority = Priority::create($priority);
        }

        // change freq from last mod
        if ($change_frequency === null && $last_modify instanceof \DateTimeInterface) {
            $change_frequency = ChangeFrequency::createByLastModify($last_modify);
        }

        // change freq from priority
        if ($change_frequency === null) {
            $change_frequency = ChangeFrequency::createByPriority($priority);
        }

        parent::__construct($location, $last_modify, $change_frequency, $priority, $languages);
    }
}
