<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream\Scope;

use GpsLab\Component\Sitemap\Location;
use GpsLab\Component\Sitemap\Stream\Exception\InvalidScopeException;
use GpsLab\Component\Sitemap\Stream\Scope\LocationScope;
use PHPUnit\Framework\TestCase;

final class LocationScopeTest extends TestCase
{
    /**
     * @return string[][]
     */
    public function getInvalidScopes(): array
    {
        return [
            // invalid URL
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
            // invalid scope
            ['https://example.com'],
            ['https://example.com/news/index.html'],
            ['https://example.com#news'],
            ['https://example.com?foo=bar'],
            ['https://example.com?foo=bar&baz=123'],
        ];
    }

    /**
     * @dataProvider getInvalidScopes
     *
     * @param string $scope
     */
    public function testInvalidScope(string $scope): void
    {
        $this->expectException(InvalidScopeException::class);

        new LocationScope($scope);
    }

    /**
     * @return array<string, array<int, string|bool>>
     */
    public function getScopes(): array
    {
        return [
            'another scheme' => ['https://example.com/', 'http://example.com/', false],
            'another port' => ['https://example.com:80/', 'https://example.com:8080/', false],
            'another domain' => ['https://example.com/', 'https://example.org/', false],
            'another path' => ['https://example.com/news/', 'https://example.com/article/', false],
            'parent path' => ['https://example.com/news/', 'https://example.com/', false],
            'root path' => ['https://example.com/', 'https://example.com/', true],
            'page in root path' => ['https://example.com/', 'https://example.com/contacts.html', true],
            'sub folder' => ['https://example.com/news/', 'https://example.com/news/', true],
            'page in sub folder' => ['https://example.com/news/', 'https://example.com/news/index.html', true],
        ];
    }

    /**
     * @dataProvider getScopes
     *
     * @param string $scope
     * @param string $url
     * @param bool   $in_scope
     */
    public function testScope(string $scope, string $url, bool $in_scope): void
    {
        $location_scope = new LocationScope($scope);

        self::assertSame($scope, $location_scope->getScope());
        self::assertSame($scope, (string) $location_scope);

        if ($in_scope) {
            self::assertTrue($location_scope->inScope(new Location($url)));
        } else {
            self::assertFalse($location_scope->inScope(new Location($url)));
        }
    }
}
