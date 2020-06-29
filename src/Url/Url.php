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

class Url
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
     * @param Location|string                $location
     * @param \DateTimeInterface|null        $last_modify
     * @param ChangeFrequency|string|null    $change_frequency
     * @param Priority|string|float|int|null $priority
     * @param array<string, string>          $languages
     *
     * @throws InvalidLastModifyException
     */
    public function __construct(
        $location,
        ?\DateTimeInterface $last_modify = null,
        $change_frequency = null,
        $priority = null,
        array $languages = []
    ) {
        if ($last_modify instanceof \DateTimeInterface && $last_modify->getTimestamp() > time()) {
            throw InvalidLastModifyException::lookToFuture($last_modify);
        }

        if ($change_frequency !== null && !$change_frequency instanceof ChangeFrequency) {
            $change_frequency = ChangeFrequency::create($change_frequency);
        }

        if ($priority !== null && !$priority instanceof Priority) {
            $priority = Priority::create($priority);
        }

        $this->location = $location instanceof Location ? $location : new Location($location);
        $this->last_modify = $last_modify;
        $this->change_frequency = $change_frequency;
        $this->priority = $priority;

        foreach ($languages as $language => $language_location) {
            $this->languages[$language] = new Language($language, $language_location);
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
     * @param array<string, string>          $languages          language versions of the page on the same domain
     * @param \DateTimeInterface|null        $last_modify
     * @param string|null                    $change_frequency
     * @param Priority|string|float|int|null $priority
     * @param array<string, string>          $external_languages language versions of the page on external domains
     *
     * @return Url[]
     */
    public static function createLanguageUrls(
        array $languages,
        ?\DateTimeInterface $last_modify = null,
        ?string $change_frequency = null,
        $priority = null,
        array $external_languages = []
    ): array {
        $external_languages = array_replace($external_languages, $languages);
        $urls = [];

        foreach (array_unique(array_values($languages)) as $location) {
            $urls[] = new self(
                $location,
                $last_modify,
                $change_frequency,
                $priority,
                $external_languages
            );
        }

        return $urls;
    }
}
