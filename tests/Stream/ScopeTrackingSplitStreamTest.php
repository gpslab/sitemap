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
use GpsLab\Component\Sitemap\Stream\ScopeTrackingSplitStream;
use GpsLab\Component\Sitemap\Stream\SplitStream;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\TestCase;

final class ScopeTrackingSplitStreamTest extends TestCase
{
    public function testOpenClose(): void
    {
        $opened = false;
        $closed = false;

        $wrapped_stream = $this->createMock(SplitStream::class);
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

        $stream = new ScopeTrackingSplitStream($wrapped_stream, 'https://example.com/');
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

        new ScopeTrackingSplitStream($this->createMock(SplitStream::class), $scope);
    }

    /**
     * @return string[][]
     */
    public function getPushOutOfScopeUrls(): array
    {
        return [
            'another scheme' => ['https://example.com/', 'http://example.com/'],
            'another port' => ['https://example.com:80/', 'https://example.com:8080/'],
            'another domain' => ['https://example.com/', 'https://example.org/'],
            'another path' => ['https://example.com/news/', 'https://example.com/article/'],
            'parent path' => ['https://example.com/news/', 'https://example.com/'],
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

        $stream = new ScopeTrackingSplitStream($this->createMock(SplitStream::class), $scope);
        $stream->push(Url::create($url));
    }

    /**
     * @return string[][]
     */
    public function getPushUrls(): array
    {
        return [
            'root path' => ['https://example.com/', 'https://example.com/'],
            'page in root path' => ['https://example.com/', 'https://example.com/index.html'],
            'sub folder' => ['https://example.com/news/', 'https://example.com/news/'],
            'page in sub folder' => ['https://example.com/news/', 'https://example.com/news/index.html'],
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
        $url = Url::create($url);

        $wrapped_stream = $this->createMock(SplitStream::class);
        $wrapped_stream
            ->expects(self::once())
            ->method('push')
            ->with($url)
        ;

        $stream = new ScopeTrackingSplitStream($wrapped_stream, $scope);
        $stream->push($url);
    }

    /**
     * @return string[][]
     */
    public function getSitemapsOutOfScope(): array
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
     * @dataProvider getSitemapsOutOfScope
     *
     * @param string $scope
     * @param string $url
     */
    public function testGetSitemapsOutOfScope(string $scope, string $url): void
    {
        $this->expectException(OutOfScopeException::class);

        $wrapped_stream = $this->createMock(SplitStream::class);
        $wrapped_stream
            ->expects(self::once())
            ->method('getSitemaps')
            ->willReturn(new \ArrayIterator([new Sitemap($url)]))
        ;

        $stream = new ScopeTrackingSplitStream($wrapped_stream, $scope);

        foreach ($stream->getSitemaps() as $sitemap) {
            self::assertInstanceOf(Sitemap::class, $sitemap);
            self::assertSame($url, (string) $sitemap->getLocation());
        }
    }

    /**
     * @return string[][]
     */
    public function getSitemaps(): array
    {
        return [
            ['https://example.com/', 'https://example.com/sitemap.xml'],
            ['https://example.com/catalog/', 'https://example.com/catalog/sitemap.xml'],
        ];
    }

    /**
     * @dataProvider getSitemaps
     *
     * @param string $scope
     * @param string $url
     */
    public function testGetSitemaps(string $scope, string $url): void
    {
        $wrapped_stream = $this->createMock(SplitStream::class);
        $wrapped_stream
            ->expects(self::once())
            ->method('getSitemaps')
            ->willReturn(new \ArrayIterator([new Sitemap($url)]))
        ;

        $stream = new ScopeTrackingSplitStream($wrapped_stream, $scope);

        foreach ($stream->getSitemaps() as $sitemap) {
            self::assertInstanceOf(Sitemap::class, $sitemap);
            self::assertSame($url, (string) $sitemap->getLocation());
        }
    }
}
