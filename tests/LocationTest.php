<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests;

use GpsLab\Component\Sitemap\Location;
use PHPUnit\Framework\TestCase;

class LocationTest extends TestCase
{
    /**
     * @return array
     */
    public function getLocations(): array
    {
        return [
            ['', true],
            ['/', true],
            ['#about', true],
            ['?foo=bar', true],
            ['?foo=bar&baz=123', true],
            ['/index.html', true],
            ['/about/index.html', true],
            ['../', false],
            ['index.html', false],
            ['&foo=bar', false],
            ['№', false],
            ['@', false],
            ['\\', false],
        ];
    }

    /**
     * @dataProvider getLocations
     *
     * @param string $locations
     * @param bool   $valid
     */
    public function testIsValid(string $locations, bool $valid): void
    {
        $this->assertEquals($valid, Location::isValid($locations));
    }
}