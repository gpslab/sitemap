<?php
declare(strict_types=1);

/**
 * This file is part of the Karusel project.
 *
 * @copyright 2010-2020 АО «Карусель» <webmaster@karusel-tv.ru>
 */

namespace GpsLab\Component\Sitemap\Url;

use GpsLab\Component\Sitemap\Location;
use GpsLab\Component\Sitemap\Url\Exception\InvalidPriorityException;

final class Priority
{
    public const AVAILABLE_PRIORITY = ['0.0', '0.1', '0.2', '0.3', '0.4', '0.5', '0.6', '0.7', '0.8', '0.9', '1.0'];

    /**
     * @var string
     */
    private $priority;

    /**
     * @var Priority[]
     */
    private static $instances = [];

    /**
     * @param string $priority
     */
    private function __construct(string $priority)
    {
        $this->priority = $priority;
    }

    /**
     * Safe creation with a limited number of object instances.
     *
     * @param string $priority
     *
     * @return self
     */
    private static function safeCreate(string $priority): self
    {
        if (!isset(self::$instances[$priority])) {
            self::$instances[$priority] = new self($priority);
        }

        return self::$instances[$priority];
    }

    /**
     * @param string|float|int $priority
     *
     * @return self
     */
    public static function create($priority): self
    {
        if (is_int($priority)) {
            if ($priority < 0 || $priority > 10) {
                throw InvalidPriorityException::invalidInteger($priority);
            }

            return self::safeCreate(number_format($priority / 10, 1));
        }

        if (is_float($priority)) {
            if ($priority < 0 || $priority > 1) {
                throw InvalidPriorityException::invalidFloat($priority);
            }

            return self::safeCreate(number_format($priority, 1));
        }

        if (is_string($priority)) {
            if (!in_array($priority, self::AVAILABLE_PRIORITY, true)) {
                throw InvalidPriorityException::invalidString($priority);
            }

            return self::safeCreate($priority);
        }

        throw InvalidPriorityException::unsupportedType($priority);
    }

    /**
     * @param Location $location
     *
     * @return Priority
     */
    public static function createByLocation(Location $location): Priority
    {
        // number of slashes
        $num = count(array_filter(explode('/', trim((string) $location, '/'))));

        if (!$num) {
            return self::safeCreate('1.0');
        }

        if (($p = (10 - $num) / 10) > 0) {
            return self::create((int) ($p * 10));
        }

        return self::safeCreate('0.1');
    }

    /**
     * @return string
     */
    public function getPriority(): string
    {
        return $this->priority;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->priority;
    }
}
