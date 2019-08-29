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
    private $change_freq;

    /**
     * @var string|null
     */
    private $priority;

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
        if (!$this->isValidLocation($location)) {
            throw InvalidLocationException::invalid($location);
        }

        if ($priority !== null && !Priority::isValid($priority)) {
            throw InvalidPriorityException::invalid($priority);
        }

        $this->location = $location;
        $this->last_modify = $last_modify;
        $this->change_freq = $change_freq;
        $this->priority = $priority;
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
    public function getChangeFreq(): ?string
    {
        return $this->change_freq;
    }

    /**
     * @return string|null
     */
    public function getPriority(): ?string
    {
        return $this->priority;
    }

    /**
     * @param string $location
     *
     * @return bool
     */
    private function isValidLocation(string $location): bool
    {
        if ($location === '') {
            return true;
        }

        if (!in_array($location[0], ['/', '?', '#'], true)) {
            return false;
        }

        return false !== filter_var(sprintf('https://example.com%s', $location), FILTER_VALIDATE_URL);
    }
}
