<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Url;

use GpsLab\Component\Sitemap\Url\Priority;
use PHPUnit\Framework\TestCase;

class PriorityTest extends TestCase
{
    /**
     * @return array
     */
    public function getPriorityOfLocations(): array
    {
        return [
            ['/', '1.0'],
            ['/index.html', '0.9'],
            ['/catalog', '0.9'],
            ['/catalog/123', '0.8'],
            ['/catalog/123/article', '0.7'],
            ['/catalog/123/article/456', '0.6'],
            ['/catalog/123/article/456/print', '0.5'],
            ['/catalog/123/subcatalog/789/article/456', '0.4'],
            ['/catalog/123/subcatalog/789/article/456/print', '0.3'],
            ['/catalog/123/subcatalog/789/article/456/print/foo', '0.2'],
            ['/catalog/123/subcatalog/789/article/456/print/foo/bar', '0.1'],
            ['/catalog/123/subcatalog/789/article/456/print/foo/bar/baz', '0.1'],
            ['/catalog/123/subcatalog/789/article/456/print/foo/bar/baz/qux', '0.1'],
        ];
    }

    /**
     * @dataProvider getPriorityOfLocations
     *
     * @param string $location
     * @param string $priority
     */
    public function testGetPriorityByLocation(string $location, string $priority): void
    {
        self::assertEquals($priority, Priority::getByLocation($location));
    }

    /**
     * @return array
     */
    public function getValidPriorities(): array
    {
        return [
            ['1.0', true],
            ['0.9', true],
            ['0.8', true],
            ['0.7', true],
            ['0.6', true],
            ['0.5', true],
            ['0.4', true],
            ['0.3', true],
            ['0.2', true],
            ['0.1', true],
            ['0.0', true],
            ['1.1', false],
            ['0.10', false],
            ['1', false],
            ['0', false],
            ['1.', false],
            ['.1', false],
            ['0.', false],
            ['.0', false],
            ['-', false],
            ['', false],
        ];
    }

    /**
     * @dataProvider getValidPriorities
     *
     * @param string $priority
     * @param bool   $is_valid
     */
    public function testIsValid(string $priority, bool $is_valid): void
    {
        self::assertEquals($is_valid, Priority::isValid($priority));
    }
}
