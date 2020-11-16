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
use GpsLab\Component\Sitemap\Url\Exception\LocationTooLongException;

final class Location
{
    /**
     * The location must be less than 2048 characters.
     */
    public const MAX_LENGTH = 2047;

    /**
     * @var string
     */
    private $location;

    /**
     * @param string $location
     *
     * @throws InvalidLocationException
     */
    public function __construct(string $location)
    {
        // this is not a true check because it does not take into account the length of the web path
        // that is added in a stream render
        if (strlen($location) > self::MAX_LENGTH) {
            throw LocationTooLongException::tooLong($location, self::MAX_LENGTH);
        }

        if (filter_var($location, FILTER_VALIDATE_URL) === false) {
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
