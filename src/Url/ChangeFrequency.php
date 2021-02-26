<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url;

use GpsLab\Component\Sitemap\Url\Exception\InvalidChangeFrequencyException;

/**
 * How frequently the page is likely to change.
 *
 * This value provides general information to search engines and may not correlate exactly to how often they crawl
 * the page. Please note that the value of this tag is considered a hint and not a command. Even though search engine
 * crawlers may consider this information when making decisions, they may crawl pages marked "hourly" less frequently
 * than that, and they may crawl pages marked "yearly" more frequently than that. Crawlers may periodically crawl pages
 * marked "never" so that they can handle unexpected changes to those pages.
 */
final class ChangeFrequency
{
    /**
     * This value should be used to describe documents that change each time they are accessed.
     */
    public const ALWAYS = 'always';

    public const HOURLY = 'hourly';

    public const DAILY = 'daily';

    public const WEEKLY = 'weekly';

    public const MONTHLY = 'monthly';

    public const YEARLY = 'yearly';

    /**
     * This value should be used to describe archived URLs.
     */
    public const NEVER = 'never';

    public const AVAILABLE_CHANGE_FREQUENCY = [
        self::ALWAYS,
        self::HOURLY,
        self::DAILY,
        self::WEEKLY,
        self::MONTHLY,
        self::YEARLY,
        self::NEVER,
    ];

    private const CHANGE_FREQUENCY_PRIORITY = [
        '0.0' => self::NEVER,
        '0.1' => self::YEARLY,
        '0.2' => self::YEARLY,
        '0.3' => self::MONTHLY,
        '0.4' => self::MONTHLY,
        '0.5' => self::WEEKLY,
        '0.6' => self::WEEKLY,
        '0.7' => self::WEEKLY,
        '0.8' => self::DAILY,
        '0.9' => self::DAILY,
        '1.0' => self::HOURLY,
    ];

    private const CHANGE_FREQUENCY_DAYS = [
        365 => self::YEARLY,
        30 => self::MONTHLY,
        7 => self::WEEKLY,
        1 => self::DAILY,
    ];

    /**
     * @var string
     */
    private $change_frequency;

    /**
     * @var ChangeFrequency[]
     */
    private static $instances = [];

    /**
     * @param string $change_frequency
     */
    private function __construct(string $change_frequency)
    {
        $this->change_frequency = $change_frequency;
    }

    /**
     * Create by value.
     *
     * @param string $change_frequency
     *
     * @throws InvalidChangeFrequencyException
     *
     * @return self
     */
    public static function create(string $change_frequency): self
    {
        if (!in_array($change_frequency, self::AVAILABLE_CHANGE_FREQUENCY, true)) {
            throw InvalidChangeFrequencyException::invalid($change_frequency);
        }

        return self::safeCreate($change_frequency);
    }

    /**
     * Safe creation with a limited number of object instances.
     *
     * @param string $change_frequency
     *
     * @return self
     */
    private static function safeCreate(string $change_frequency): self
    {
        if (!isset(self::$instances[$change_frequency])) {
            self::$instances[$change_frequency] = new self($change_frequency);
        }

        return self::$instances[$change_frequency];
    }

    /**
     * This value should be used to describe documents that change each time they are accessed.
     *
     * @return self
     */
    public static function always(): self
    {
        return self::safeCreate(self::ALWAYS);
    }

    /**
     * @return self
     */
    public static function hourly(): self
    {
        return self::safeCreate(self::HOURLY);
    }

    /**
     * @return self
     */
    public static function daily(): self
    {
        return self::safeCreate(self::DAILY);
    }

    /**
     * @return self
     */
    public static function weekly(): self
    {
        return self::safeCreate(self::WEEKLY);
    }

    /**
     * @return self
     */
    public static function monthly(): self
    {
        return self::safeCreate(self::MONTHLY);
    }

    /**
     * @return self
     */
    public static function yearly(): self
    {
        return self::safeCreate(self::YEARLY);
    }

    /**
     * This value should be used to describe archived URLs.
     *
     * @return self
     */
    public static function never(): self
    {
        return self::safeCreate(self::NEVER);
    }

    /**
     * @param \DateTimeInterface $last_modify
     *
     * @return self
     */
    public static function createByLastModify(\DateTimeInterface $last_modify): self
    {
        $diff = $last_modify->diff(new \DateTimeImmutable());

        foreach (self::CHANGE_FREQUENCY_DAYS as $days => $change_frequency) {
            if ($diff->days >= $days) {
                return self::safeCreate($change_frequency);
            }
        }

        return self::safeCreate(self::HOURLY);
    }

    /**
     * @param Priority $priority
     *
     * @return self|null
     */
    public static function createByPriority(Priority $priority): ?self
    {
        return self::safeCreate(self::CHANGE_FREQUENCY_PRIORITY[$priority->getPriority()]);
    }

    /**
     * @return string
     */
    public function getChangeFrequency(): string
    {
        return $this->change_frequency;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->change_frequency;
    }
}
