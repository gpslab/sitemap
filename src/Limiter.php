<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap;

use GpsLab\Component\Sitemap\Stream\Exception\LinksOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\SitemapsOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\SizeOverflowException;

final class Limiter
{
    /**
     * The maximum number of URLs in a sitemap.
     */
    public const LINKS_LIMIT = 50000;

    /**
     * The maximum number of sitemaps in a sitemap index.
     */
    public const SITEMAPS_LIMIT = 50000;

    /**
     * The maximum size of sitemap.xml in bytes.
     */
    public const BYTE_LIMIT = 52428800; // 50 Mb

    /**
     * @var int
     */
    private $added_urls = 0;

    /**
     * @var int
     */
    private $added_sitemaps = 0;

    /**
     * @var int
     */
    private $used_bytes = 0;

    /**
     * @throws LinksOverflowException
     */
    public function tryAddUrl(): void
    {
        if ($this->added_urls + 1 > self::LINKS_LIMIT) {
            throw LinksOverflowException::withLimit(self::LINKS_LIMIT);
        }

        ++$this->added_urls;
    }

    /**
     * @return int
     */
    public function howManyUrlsAvailableToAdd(): int
    {
        return self::LINKS_LIMIT - $this->added_urls;
    }

    /**
     * @throws SitemapsOverflowException
     */
    public function tryAddSitemap(): void
    {
        if ($this->added_sitemaps + 1 > self::SITEMAPS_LIMIT) {
            throw SitemapsOverflowException::withLimit(self::SITEMAPS_LIMIT);
        }

        ++$this->added_sitemaps;
    }

    /**
     * @return int
     */
    public function howManySitemapsAvailableToAdd(): int
    {
        return self::SITEMAPS_LIMIT - $this->added_sitemaps;
    }

    /**
     * @param int $used_bytes
     *
     * @throws SizeOverflowException
     */
    public function tryUseBytes(int $used_bytes): void
    {
        if ($this->used_bytes + $used_bytes > self::BYTE_LIMIT) {
            throw SizeOverflowException::withLimit(self::BYTE_LIMIT);
        }

        $this->used_bytes += $used_bytes;
    }

    /**
     * @return int
     */
    public function howManyBytesAvailableToUse(): int
    {
        return self::BYTE_LIMIT - $this->used_bytes;
    }

    public function reset(): void
    {
        $this->added_urls = 0;
        $this->added_sitemaps = 0;
        $this->used_bytes = 0;
    }
}
