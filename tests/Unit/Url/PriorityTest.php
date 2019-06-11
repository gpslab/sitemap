<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Unit\Url;

use GpsLab\Component\Sitemap\Url\Priority;
use PHPUnit\Framework\TestCase;

class PriorityTest extends TestCase
{
    /**
     * @return array
     */
    public function priorityOfLocations(): array
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
     * @dataProvider priorityOfLocations
     *
     * @param string $loc
     * @param string $priority
     */
    public function testGetPriorityByLoc(string $loc, string $priority): void
    {
        self::assertEquals($priority, Priority::getByLoc($loc));
    }
}
