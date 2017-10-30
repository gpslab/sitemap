<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\Exception;

class FileAccessException extends \RuntimeException
{
    /**
     * @param string $filename
     *
     * @return static
     */
    final public static function notWritable($filename)
    {
        return new static(sprintf('File "%s" is not writable.', $filename));
    }
}
