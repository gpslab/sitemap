<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Writer\Exception;

final class CompressionWindowException extends InvalidCompressionArgumentException
{
    /**
     * @param mixed $current_size
     * @param int   $min_size
     * @param int   $max_size
     *
     * @return self
     */
    public static function invalid($current_size, int $min_size, int $max_size): self
    {
        return new self(sprintf(
            'The zlib window size "%s" must be in interval [%d, %d].',
            $current_size,
            $min_size,
            $max_size
        ));
    }
}
