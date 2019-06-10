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

final class FileAccessException extends \RuntimeException
{
    /**
     * @param string $filename
     *
     * @return self
     */
    public static function notWritable(string $filename): self
    {
        return new self(sprintf('File "%s" is not writable.', $filename));
    }
}
