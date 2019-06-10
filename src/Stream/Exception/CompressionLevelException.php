<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\Exception;

final class CompressionLevelException extends \InvalidArgumentException
{
    /**
     * @param int $current_level
     * @param int $min_level
     * @param int $max_level
     *
     * @return self
     */
    public static function invalid(int $current_level, int $min_level, int $max_level): self
    {
        return new self(sprintf(
            'Compression level "%s" must be in interval [%d, %d].',
            $current_level,
            $min_level,
            $max_level
        ));
    }
}
