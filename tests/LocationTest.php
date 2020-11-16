<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests;

use GpsLab\Component\Sitemap\Exception\InvalidLocationException;
use GpsLab\Component\Sitemap\Location;
use GpsLab\Component\Sitemap\Url\Exception\LocationTooLongException;
use PHPUnit\Framework\TestCase;

final class LocationTest extends TestCase
{
    /**
     * @return string[][]
     */
    public function getValidLocations(): array
    {
        return [
            ['https://example.com'],
            ['https://example.com/'],
            ['https://example.com#about'],
            ['https://example.com?foo=bar'],
            ['https://example.com?foo=bar&baz=123'],
            ['https://example.com/index.html'],
            ['https://example.com/about/index.html'],
        ];
    }

    /**
     * @dataProvider getValidLocations
     *
     * @param string $location
     */
    public function testValidLocation(string $location): void
    {
        $object = new Location($location);

        $this->assertSame($location, $object->getLocation());
        $this->assertSame($location, (string) $object);
    }

    /**
     * @return string[][]
     */
    public function getInvalidLocations(): array
    {
        return [
            [''],
            ['/'],
            ['../'],
            ['index.html'],
            ['?foo=bar'],
            ['&foo=bar'],
            ['#'],
            ['№'],
            ['@'],
            ['\\'],
        ];
    }

    /**
     * @dataProvider getInvalidLocations
     *
     * @param string $location
     */
    public function testInvalidLocation(string $location): void
    {
        $this->expectException(InvalidLocationException::class);

        new Location($location);
    }

    public function testLocationTooLong(): void
    {
        $this->expectException(LocationTooLongException::class);

        $location = 'https://example.com/';
        $location .= str_repeat('f', Location::MAX_LENGTH - strlen($location) + 1 /* overflow */);

        new Location($location);
    }
}
