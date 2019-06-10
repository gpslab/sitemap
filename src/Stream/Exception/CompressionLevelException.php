<?php
declare(strict_types=1);

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
     * @param mixed $current_level
     * @param int   $min_level
     * @param int   $max_level
     *
     * @return self
     */
    public static function invalid($current_level, int $min_level, int $max_level): self
    {
        return new self(sprintf(
            'Compression level "%s" must be in interval [%d, %d].',
            $current_level,
            $min_level,
            $max_level
        ));
    }
}
