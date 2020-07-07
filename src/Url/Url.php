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
use GpsLab\Component\Sitemap\Url\Exception\InvalidLastModifyException;

final class Url
{
    /**
     * @var Location
     */
    private $location;

    /**
     * @var \DateTimeInterface|null
     */
    private $last_modify;

    /**
     * @var ChangeFrequency|null
     */
    private $change_frequency;

    /**
     * @var Priority|null
     */
    private $priority;

    /**
     * @var array<string, Language>
     */
    private $languages = [];

    /**
     * @param Location                $location
     * @param \DateTimeInterface|null $last_modify
     * @param ChangeFrequency|null    $change_frequency
     * @param Priority|null           $priority
     * @param Language[]              $languages
     *
     * @throws InvalidLastModifyException
     */
    public function __construct(
        Location $location,
        ?\DateTimeInterface $last_modify = null,
        ?ChangeFrequency $change_frequency = null,
        ?Priority $priority = null,
        array $languages = []
    ) {
        if ($last_modify instanceof \DateTimeInterface && $last_modify->getTimestamp() > time()) {
            throw InvalidLastModifyException::lookToFuture($last_modify);
        }

        $this->location = $location;
        $this->last_modify = $last_modify;
        $this->change_frequency = $change_frequency;
        $this->priority = $priority;

        foreach ($languages as $language) {
            $this->addLanguage($language);
        }
    }

    /**
     * @return Location
     */
    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getLastModify(): ?\DateTimeInterface
    {
        return $this->last_modify;
    }

    /**
     * @return ChangeFrequency|null
     */
    public function getChangeFrequency(): ?ChangeFrequency
    {
        return $this->change_frequency;
    }

    /**
     * @return Priority|null
     */
    public function getPriority(): ?Priority
    {
        return $this->priority;
    }

    /**
     * @return Language[]
     */
    public function getLanguages(): array
    {
        return array_values($this->languages);
    }

    /**
     * @param Language $language
     */
    private function addLanguage(Language $language): void
    {
        $this->languages[$language->getLanguage()] = $language;
    }

    /**
     * Simplified URL creation from basic data types.
     *
     * @param Location|string                  $location
     * @param \DateTimeInterface|null          $last_modify
     * @param ChangeFrequency|string|null      $change_frequency
     * @param Priority|string|float|int|null   $priority
     * @param array<string, string>|Language[] $languages
     *
     * @return self
     */
    public static function create(
        $location,
        ?\DateTimeInterface $last_modify = null,
        $change_frequency = null,
        $priority = null,
        array $languages = []
    ): self {
        if (!$location instanceof Location) {
            $location = new Location($location);
        }

        if ($change_frequency !== null && !$change_frequency instanceof ChangeFrequency) {
            $change_frequency = ChangeFrequency::create($change_frequency);
        }

        if ($priority !== null && !$priority instanceof Priority) {
            $priority = Priority::create($priority);
        }

        $repack_languages = [];
        foreach ($languages as $language => $language_location) {
            if ($language_location instanceof Language) {
                $repack_languages[$language_location->getLanguage()] = $language_location;
            } else {
                $repack_languages[$language] = new Language($language, $language_location);
            }
        }

        return new self($location, $last_modify, $change_frequency, $priority, $repack_languages);
    }

    /**
     * Create a new URL and automatically fills fields that it can.
     *
     * @param Location|string                  $location
     * @param \DateTimeInterface|null          $last_modify
     * @param ChangeFrequency|string|null      $change_frequency
     * @param Priority|string|float|int|null   $priority
     * @param array<string, string>|Language[] $languages
     *
     * @return self
     */
    public static function createSmart(
        $location,
        ?\DateTimeInterface $last_modify = null,
        $change_frequency = null,
        $priority = null,
        array $languages = []
    ): self {
        if (!$location instanceof Location) {
            $location = new Location($location);
        }

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

        return self::create($location, $last_modify, $change_frequency, $priority, $languages);
    }

    /**
     * Create cross-URLs for several languages.
     *
     * @param array<string, string>          $languages          language versions of the page on the same domain
     * @param \DateTimeInterface|null        $last_modify
     * @param ChangeFrequency|string|null    $change_frequency
     * @param Priority|string|float|int|null $priority
     * @param array<string, string>          $external_languages language versions of the page on external domains
     *
     * @return Url[]
     */
    public static function createLanguageUrls(
        array $languages,
        ?\DateTimeInterface $last_modify = null,
        $change_frequency = null,
        $priority = null,
        array $external_languages = []
    ): array {
        $external_languages = array_replace($external_languages, $languages);

        $urls = [];
        foreach (array_unique(array_values($languages)) as $location) {
            $urls[] = self::create($location, $last_modify, $change_frequency, $priority, $external_languages);
        }

        return $urls;
    }
}
