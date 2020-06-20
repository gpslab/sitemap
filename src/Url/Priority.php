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
            $priority = number_format($priority / 10, 1);
        } elseif (is_float($priority)) {
            $priority = number_format($priority, 1);
        }

        if (!in_array($priority, self::AVAILABLE_PRIORITY, true)) {
            throw InvalidPriorityException::invalid($priority);
        }

        return self::safeCreate($priority);
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
            return self::safeCreate(number_format($p, 1));
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
