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

/**
 * The priority of this URL relative to other URLs on your site.
 *
 * Valid values range from 0.0 to 1.0. This value does not affect how your pages are compared to pages on other
 * sites—it only lets the search engines know which pages you deem most important for the crawlers.
 *
 * The default priority of a page is 0.5.
 *
 * Please note that the priority you assign to a page is not likely to influence the position of your URLs in a search
 * engine's result pages. Search engines may use this information when selecting between URLs on the same site, so you
 * can use this tag to increase the likelihood that your most important pages are present in a search index.
 *
 * Also, please note that assigning a high priority to all of the URLs on your site is not likely to help you. Since
 * the priority is relative, it is only used to select between URLs on your site.
 */
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
     * @throws InvalidPriorityException
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
     * @return self
     */
    public static function createByLocation(Location $location): self
    {
        $path = (string) parse_url($location->getLocation(), PHP_URL_PATH);
        $path = trim($path, '/');
        $path_nesting_level = count(array_filter(explode('/', $path)));

        if ($path_nesting_level === 0) {
            return self::safeCreate('1.0');
        }

        $priority = (10 - $path_nesting_level) / 10;

        if ($priority > 0) {
            return self::safeCreate(number_format($priority, 1));
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
