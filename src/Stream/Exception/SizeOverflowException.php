<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\Exception;

final class SizeOverflowException extends OverflowException
{
    /**
     * @param int $byte_limit
     *
     * @return self
     */
    public static function withLimit(int $byte_limit): self
    {
        return new self(sprintf('The limit of %d byte in the sitemap.xml was exceeded.', $byte_limit));
    }
}
