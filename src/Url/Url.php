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

class Url
{
    public const DEFAULT_PRIORITY = '1.0';

    public const DEFAULT_CHANGE_FREQ = ChangeFreq::WEEKLY;

    /**
     * @var string
     */
    private $location;

    /**
     * @var \DateTimeInterface
     */
    private $last_modify;

    /**
     * @var string
     */
    private $change_freq;

    /**
     * @var string
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
        $this->location = $location;
        $this->last_modify = $last_modify ?: new \DateTimeImmutable();
        $this->change_freq = $change_freq ?: self::DEFAULT_CHANGE_FREQ;
        $this->priority = $priority ?: self::DEFAULT_PRIORITY;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastModify(): \DateTimeInterface
    {
        return $this->last_modify;
    }

    /**
     * @return string
     */
    public function getChangeFreq(): string
    {
        return $this->change_freq;
    }

    /**
     * @return string
     */
    public function getPriority(): string
    {
        return $this->priority;
    }
}
