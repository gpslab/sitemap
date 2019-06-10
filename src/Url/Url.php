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

class Url
{
    public const DEFAULT_PRIORITY = '1.0';

    public const DEFAULT_CHANGE_FREQ = ChangeFreq::WEEKLY;

    /**
     * @var string
     */
    private $loc = '';

    /**
     * @var \DateTimeInterface
     */
    private $last_mod;

    /**
     * @var string
     */
    private $change_freq = '';

    /**
     * @var string
     */
    private $priority = '';

    /**
     * @param string                  $loc
     * @param \DateTimeInterface|null $last_mod
     * @param string|null             $change_freq
     * @param string|null             $priority
     */
    public function __construct(
        string $loc,
        ?\DateTimeInterface $last_mod = null,
        ?string $change_freq = null,
        ?string $priority = null
    ) {
        $this->loc = $loc;
        $this->last_mod = $last_mod ?: new \DateTimeImmutable();
        $this->change_freq = $change_freq ?: self::DEFAULT_CHANGE_FREQ;
        $this->priority = $priority ?: self::DEFAULT_PRIORITY;
    }

    /**
     * @return string
     */
    public function getLoc(): string
    {
        return $this->loc;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastMod(): \DateTimeInterface
    {
        return $this->last_mod;
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
