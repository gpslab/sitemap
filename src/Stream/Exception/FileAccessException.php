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

    /**
     * @param string $filename
     * @param string $string
     *
     * @return static
     */
    final public static function failedWrite($filename, $string)
    {
        return new static(sprintf('Failed write string "%s" to file "%s".', $string, $filename));
    }
}
