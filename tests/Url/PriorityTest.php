<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Url;

use GpsLab\Component\Sitemap\Location;
use GpsLab\Component\Sitemap\Url\Exception\InvalidPriorityException;
use GpsLab\Component\Sitemap\Url\Priority;
use PHPUnit\Framework\TestCase;

final class PriorityTest extends TestCase
{
    /**
     * @return array<int, array<int, string|float|int>>
     */
    public function getValidPriorities(): array
    {
        return [
            [10, '1.0'],
            [1.0, '1.0'],
            ['1.0', '1.0'],
            [9, '0.9'],
            [.9, '0.9'],
            ['0.9', '0.9'],
            [8, '0.8'],
            [.8, '0.8'],
            ['0.8', '0.8'],
            [7, '0.7'],
            [.7, '0.7'],
            ['0.7', '0.7'],
            [6, '0.6'],
            [.6, '0.6'],
            ['0.6', '0.6'],
            [5, '0.5'],
            [.5, '0.5'],
            ['0.5', '0.5'],
            [4, '0.4'],
            [.4, '0.4'],
            ['0.4', '0.4'],
            [3, '0.3'],
            [.3, '0.3'],
            ['0.3', '0.3'],
            [2, '0.2'],
            [.2, '0.2'],
            ['0.2', '0.2'],
            [1, '0.1'],
            [.1, '0.1'],
            ['0.1', '0.1'],
            [0, '0.0'],
            [.0, '0.0'],
            ['0.0', '0.0'],
        ];
    }

    /**
     * @dataProvider getValidPriorities
     *
     * @param string|int|float $priority
     * @param string           $expected
     */
    public function testValid($priority, string $expected): void
    {
        $object = Priority::create($priority);

        self::assertSame($expected, $object->getPriority());
        self::assertSame($expected, (string) $object);
        self::assertSame($object, Priority::create($priority));
    }

    /**
     * @return mixed[][]
     */
    public function getInvalidPriorities(): array
    {
        return [
            [11],
            [1.1],
            ['1.1'],
            [-1],
            [-1.0],
            ['-1.0'],
            ['-'],
        ];
    }

    /**
     * @dataProvider getInvalidPriorities
     *
     * @param mixed $priority
     */
    public function testInvalid($priority): void
    {
        $this->expectException(InvalidPriorityException::class);

        Priority::create($priority);
    }

    /**
     * @return array<int, array<int, string|int>>
     */
    public function getPriorityOfLocations(): array
    {
        return [
            ['https://example.com/', '1.0'],
            ['https://example.com/index.html', '0.9'],
            ['https://example.com/catalog', '0.9'],
            ['https://example.com/catalog/123', '0.8'],
            ['https://example.com/catalog/123/article', '0.7'],
            ['https://example.com/catalog/123/article/456', '0.6'],
            ['https://example.com/catalog/123/article/456/print', '0.5'],
            ['https://example.com/catalog/123/subcatalog/789/article/456', '0.4'],
            ['https://example.com/catalog/123/subcatalog/789/article/456/print', '0.3'],
            ['https://example.com/catalog/123/subcatalog/789/article/456/print/foo', '0.2'],
            ['https://example.com/catalog/123/subcatalog/789/article/456/print/foo/bar', '0.1'],
            ['https://example.com/catalog/123/subcatalog/789/article/456/print/foo/bar/baz', '0.1'],
            ['https://example.com/catalog/123/subcatalog/789/article/456/print/foo/bar/baz/qux', '0.1'],
            ['https://example.com///catalog///123', '0.8'],
        ];
    }

    /**
     * @dataProvider getPriorityOfLocations
     *
     * @param string $location
     * @param string $priority
     */
    public function testCreatePriorityByLocation(string $location, string $priority): void
    {
        self::assertEquals($priority, (string) Priority::createByLocation(new Location($location)));
    }
}
