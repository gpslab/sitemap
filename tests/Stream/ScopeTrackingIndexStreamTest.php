<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream;

use GpsLab\Component\Sitemap\Sitemap\Sitemap;
use GpsLab\Component\Sitemap\Stream\Exception\InvalidScopeException;
use GpsLab\Component\Sitemap\Stream\Exception\OutOfScopeException;
use GpsLab\Component\Sitemap\Stream\IndexStream;
use GpsLab\Component\Sitemap\Stream\ScopeTrackingIndexStream;
use PHPUnit\Framework\TestCase;

final class ScopeTrackingIndexStreamTest extends TestCase
{
    public function testOpenClose(): void
    {
        $opened = false;
        $closed = false;

        $wrapped_stream = $this->createMock(IndexStream::class);
        $wrapped_stream
            ->expects(self::once())
            ->method('open')
            ->willReturnCallback(function () use (&$opened) {
                $opened = true;
            });
        $wrapped_stream
            ->expects(self::once())
            ->method('close')
            ->willReturnCallback(function () use (&$closed) {
                $closed = true;
            });

        $stream = new ScopeTrackingIndexStream($wrapped_stream, 'https://example.com/');
        $stream->open();
        $stream->close();

        self::assertTrue($opened);
        self::assertTrue($closed);
    }

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

        new ScopeTrackingIndexStream($this->createMock(IndexStream::class), $scope);
    }

    /**
     * @return string[][]
     */
    public function getPushOutOfScopeUrls(): array
    {
        return [
            'another scheme' => ['https://example.com/', 'http://example.com/sitemap.xml'],
            'another port' => ['https://example.com:80/', 'https://example.com:8080/sitemap.xml'],
            'another domain' => ['https://example.com/', 'https://example.org/sitemap.xml'],
            'another path' => ['https://example.com/news/', 'https://example.com/article/sitemap.xml'],
            'parent path' => ['https://example.com/news/', 'https://example.com/sitemap.xml'],
        ];
    }

    /**
     * @dataProvider getPushOutOfScopeUrls
     *
     * @param string $scope
     * @param string $url
     */
    public function testPushOutOfScope(string $scope, string $url): void
    {
        $this->expectException(OutOfScopeException::class);

        $stream = new ScopeTrackingIndexStream($this->createMock(IndexStream::class), $scope);
        $stream->pushSitemap(new Sitemap($url));
    }

    /**
     * @return string[][]
     */
    public function getPushUrls(): array
    {
        return [
            'root sitemap.xml' => ['https://example.com/', 'https://example.com/sitemap.xml'],
            'section sitemap.xml' => ['https://example.com/news/', 'https://example.com/news/sitemap.xml'],
        ];
    }

    /**
     * @dataProvider getPushUrls
     *
     * @param string $scope
     * @param string $url
     */
    public function testPush(string $scope, string $url): void
    {
        $sitemap = new Sitemap($url);

        $wrapped_stream = $this->createMock(IndexStream::class);
        $wrapped_stream
            ->expects(self::once())
            ->method('pushSitemap')
            ->with($sitemap)
        ;

        $stream = new ScopeTrackingIndexStream($wrapped_stream, $scope);
        $stream->pushSitemap($sitemap);
    }
}
