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
            ['/', 1.0],
            ['/index.html', .9],
            ['/catalog', .9],
            ['/catalog/123', .8],
            ['/catalog/123/article', .7],
            ['/catalog/123/article/456', .6],
            ['/catalog/123/article/456/print', .5],
            ['/catalog/123/subcatalog/789/article/456', .4],
            ['/catalog/123/subcatalog/789/article/456/print', .3],
            ['/catalog/123/subcatalog/789/article/456/print/foo', .2],
            ['/catalog/123/subcatalog/789/article/456/print/foo/bar', .1],
            ['/catalog/123/subcatalog/789/article/456/print/foo/bar/baz', .1],
            ['/catalog/123/subcatalog/789/article/456/print/foo/bar/baz/qux', .1],
        ];
    }

    /**
     * @dataProvider getPriorityOfLocations
     *
     * @param string $location
     * @param float  $priority
     */
    public function testGetPriorityByLocation(string $location, float $priority): void
    {
        self::assertEquals($priority, Priority::getByLocation($location));
    }

    /**
     * @return array
     */
    public function getValidPriorities(): array
    {
        return [
            [1.0, true],
            [.9, true],
            [.8, true],
            [.7, true],
            [.6, true],
            [.5, true],
            [.4, true],
            [.3, true],
            [.2, true],
            [.1, true],
            [.0, true],
            [.000000000000000000000000000000001, true],
            [1.1, false],
            [-1.1, false],
            [-.1, false],
            [-.0, true],
        ];
    }

    /**
     * @dataProvider getValidPriorities
     *
     * @param float $priority
     * @param bool  $is_valid
     */
    public function testIsValid(float $priority, bool $is_valid): void
    {
        self::assertEquals($is_valid, Priority::isValid($priority));
    }
}
