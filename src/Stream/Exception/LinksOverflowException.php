<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\Exception;

final class LinksOverflowException extends OverflowException
{
    /**
     * @param int $links_limit
     *
     * @return self
     */
    public static function withLimit(int $links_limit): string
    {
        return new self(sprintf('The limit of %d URLs in the sitemap.xml was exceeded.', $links_limit));
    }
}
