<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\Exception;

class CompressionLevelException extends \InvalidArgumentException
{
    /**
     * @param int $current_level
     * @param int $min_level
     * @param int $max_level
     *
     * @return static
     */
    final public static function invalid($current_level, $min_level, $max_level)
    {
        return new static(sprintf(
            'Compression level "%s" must be in interval [%d, %d].',
            $current_level,
            $min_level,
            $max_level
        ));
    }
}
