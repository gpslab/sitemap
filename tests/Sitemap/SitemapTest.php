<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Sitemap;

use GpsLab\Component\Sitemap\Exception\InvalidLocationException;
use GpsLab\Component\Sitemap\Sitemap\Exception\InvalidLastModifyException;
use GpsLab\Component\Sitemap\Sitemap\Sitemap;
use PHPUnit\Framework\TestCase;

final class SitemapTest extends TestCase
{
    /**
     * @return array<int, array<int, string|\DateTimeInterface|null>>
     */
    public function getSitemap(): array
    {
        return [
            ['https://example.com', null],
            ['https://example.com/', new \DateTime('-1 day')],
            ['https://example.com/', new \DateTimeImmutable('-1 day')],
            ['https://example.com/index.html', null],
            ['https://example.com/about/index.html', null],
            ['https://example.com?', null],
            ['https://example.com?foo=bar', null],
            ['https://example.com?foo=bar&baz=123', null],
            ['https://example.com#', null],
            ['https://example.com#about', null],
        ];
    }

    /**
     * @dataProvider getSitemap
     *
     * @param string                  $location
     * @param \DateTimeInterface|null $last_modify
     */
    public function testSitemap(string $location, ?\DateTimeInterface $last_modify = null): void
    {
        $sitemap = new Sitemap($location, $last_modify);

        $this->assertEquals($location, (string) $sitemap->getLocation());
        $this->assertEquals($last_modify, $sitemap->getLastModify());
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

        new Sitemap($location);
    }

    public function testInvalidLastModify(): void
    {
        $this->expectException(InvalidLastModifyException::class);

        new Sitemap('https://example.com/', new \DateTimeImmutable('+1 minutes'));
    }
}
