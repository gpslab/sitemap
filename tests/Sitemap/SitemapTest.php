<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Sitemap;

use GpsLab\Component\Sitemap\Sitemap\Exception\InvalidLastModifyException;
use GpsLab\Component\Sitemap\Sitemap\Exception\InvalidLocationException;
use GpsLab\Component\Sitemap\Sitemap\Sitemap;
use PHPUnit\Framework\TestCase;

final class SitemapTest extends TestCase
{
    /**
     * @return array
     */
    public function getSitemap(): array
    {
        return [
            ['', null],
            ['/', new \DateTime('-1 day')],
            ['/', new \DateTimeImmutable('-1 day')],
            ['/index.html', null],
            ['/about/index.html', null],
            ['?', null],
            ['?foo=bar', null],
            ['?foo=bar&baz=123', null],
            ['#', null],
            ['#about', null],
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

        $this->assertEquals($location, $sitemap->getLocation());
        $this->assertEquals($last_modify, $sitemap->getLastModify());
    }

    /**
     * @return array
     */
    public function getInvalidLocations(): array
    {
        return [
            ['../'],
            ['index.html'],
            ['&foo=bar'],
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

        new Sitemap('/', new \DateTimeImmutable('+1 minutes'));
    }
}
