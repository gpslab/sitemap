<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests;

use GpsLab\Component\Sitemap\Limiter;
use GpsLab\Component\Sitemap\Stream\Exception\LinksOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\SitemapsOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\SizeOverflowException;
use PHPUnit\Framework\TestCase;

final class LimiterTest extends TestCase
{
    /**
     * @var Limiter
     */
    private $limiter;

    protected function setUp(): void
    {
        $this->limiter = new Limiter();
    }

    public function testTryAddUrl(): void
    {
        $this->limiter->tryAddUrl();
        self::assertEquals(Limiter::LINKS_LIMIT - 1, $this->limiter->howManyUrlsAvailableToAdd());
    }

    public function testTryAddUrlReset(): void
    {
        $this->limiter->tryAddUrl();
        self::assertEquals(Limiter::LINKS_LIMIT - 1, $this->limiter->howManyUrlsAvailableToAdd());
        $this->limiter->reset();
        $this->limiter->tryAddUrl();
        self::assertEquals(Limiter::LINKS_LIMIT - 1, $this->limiter->howManyUrlsAvailableToAdd());
    }

    public function testTryAddUrlOverflow(): void
    {
        for ($i = 0; $i < Limiter::LINKS_LIMIT; ++$i) {
            $this->limiter->tryAddUrl();
            self::assertEquals(Limiter::LINKS_LIMIT - ($i + 1), $this->limiter->howManyUrlsAvailableToAdd());
        }

        $this->expectException(LinksOverflowException::class);
        $this->limiter->tryAddUrl();
    }

    public function testTryAddSitemap(): void
    {
        $this->limiter->tryAddSitemap();
        self::assertEquals(Limiter::SITEMAPS_LIMIT - 1, $this->limiter->howManySitemapsAvailableToAdd());
    }

    public function testTryAddSitemapReset(): void
    {
        $this->limiter->tryAddSitemap();
        self::assertEquals(Limiter::SITEMAPS_LIMIT - 1, $this->limiter->howManySitemapsAvailableToAdd());
        $this->limiter->reset();
        $this->limiter->tryAddSitemap();
        self::assertEquals(Limiter::SITEMAPS_LIMIT - 1, $this->limiter->howManySitemapsAvailableToAdd());
    }

    public function testTryAddSitemapOverflow(): void
    {
        for ($i = 0; $i < Limiter::SITEMAPS_LIMIT; ++$i) {
            $this->limiter->tryAddSitemap();
            self::assertEquals(Limiter::SITEMAPS_LIMIT - ($i + 1), $this->limiter->howManySitemapsAvailableToAdd());
        }

        $this->expectException(SitemapsOverflowException::class);
        $this->limiter->tryAddSitemap();
    }

    public function testTryUseBytes(): void
    {
        $this->limiter->tryUseBytes(1);
        self::assertEquals(Limiter::BYTE_LIMIT - 1, $this->limiter->howManyBytesAvailableToUse());
    }

    public function testTryUseBytesReset(): void
    {
        $this->limiter->tryUseBytes(1);
        self::assertEquals(Limiter::BYTE_LIMIT - 1, $this->limiter->howManyBytesAvailableToUse());
        $this->limiter->reset();
        $this->limiter->tryUseBytes(1);
        self::assertEquals(Limiter::BYTE_LIMIT - 1, $this->limiter->howManyBytesAvailableToUse());
    }

    public function testTryUseBytesOverflow(): void
    {
        $this->limiter->tryUseBytes(Limiter::BYTE_LIMIT);
        self::assertEquals(0, $this->limiter->howManyBytesAvailableToUse());

        $this->expectException(SizeOverflowException::class);
        $this->limiter->tryUseBytes(1);
    }
}
