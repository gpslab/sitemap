<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\Exception;

final class SitemapsOverflowException extends OverflowException
{
    /**
     * @param int $sitemaps_limit
     *
     * @return self
     */
    public static function withLimit(int $sitemaps_limit): self
    {
        return new self(sprintf('The limit of %d sitemaps in the sitemap index was exceeded.', $sitemaps_limit));
    }
}
