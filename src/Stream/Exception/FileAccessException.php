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
     *
     * @return static
     */
    final public static function notReadable($filename)
    {
        return new static(sprintf('File "%s" is not readable.', $filename));
    }

    /**
     * @param string $tmp_filename
     * @param string $target_filename
     *
     * @return self
     */
    final public static function failedOverwrite($tmp_filename, $target_filename)
    {
        return new self(sprintf(
            'Failed to overwrite file "%s" from temporary file "%s".',
            $target_filename,
            $tmp_filename
        ));
    }

    /**
     * @param string $path
     * @param string $prefix
     *
     * @return self
     */
    final public static function failedCreateUnique($path, $prefix)
    {
        return new self(sprintf(
            'Failed create file with unique file name in folder "%s" with prefix "%s".',
            $path,
            $prefix
        ));
    }
}
