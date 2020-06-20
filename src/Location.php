<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap;

use GpsLab\Component\Sitemap\Exception\InvalidLocationException;

final class Location
{
    /**
     * @var string
     */
    private $location;

    /**
     * @throws InvalidLocationException
     *
     * @param string $location
     */
    public function __construct(string $location)
    {
        if (($location && !in_array($location[0], ['/', '?', '#'], true)) ||
            filter_var(sprintf('https://example.com%s', $location), FILTER_VALIDATE_URL) === false
        ) {
            throw InvalidLocationException::invalid($location);
        }

        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->location;
    }
}
