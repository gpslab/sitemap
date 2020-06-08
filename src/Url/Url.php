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
use GpsLab\Component\Sitemap\Url\Exception\InvalidChangeFrequencyException;
use GpsLab\Component\Sitemap\Url\Exception\InvalidLastModifyException;
use GpsLab\Component\Sitemap\Url\Exception\InvalidLocationException;
use GpsLab\Component\Sitemap\Url\Exception\InvalidPriorityException;

class Url
{
    /**
     * @var string
     */
    private $location;

    /**
     * @var \DateTimeInterface|null
     */
    private $last_modify;

    /**
     * @var string|null
     */
    private $change_frequency;

    /**
     * @var int|null
     */
    private $priority;

    /**
     * @var array
     */
    private $languages;

    /**
     * @param string                  $location
     * @param \DateTimeInterface|null $last_modify
     * @param string|null             $change_frequency
     * @param int|null                $priority
     * @param array                   $languages
     */
    public function __construct(
        string $location,
        ?\DateTimeInterface $last_modify = null,
        ?string $change_frequency = null,
        ?int $priority = null,
        array $languages = []
    ) {
        if (!Location::isValid($location)) {
            throw InvalidLocationException::invalid($location);
        }

        if ($last_modify instanceof \DateTimeInterface && $last_modify->getTimestamp() > time()) {
            throw InvalidLastModifyException::lookToFuture($last_modify);
        }

        if ($change_frequency !== null && !ChangeFrequency::isValid($change_frequency)) {
            throw InvalidChangeFrequencyException::invalid($change_frequency);
        }

        if ($priority !== null && !Priority::isValid($priority)) {
            throw InvalidPriorityException::invalid($priority);
        }

        $this->location = $location;
        $this->last_modify = $last_modify;
        $this->change_frequency = $change_frequency;
        $this->priority = $priority;
        $this->languages = $languages;
    }

    /**
     * @return string
     */
    public function getLocation(): string
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
     * @return string|null
     */
    public function getChangeFrequency(): ?string
    {
        return $this->change_frequency;
    }

    /**
     * @return int|null
     */
    public function getPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * @return array
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }
}
