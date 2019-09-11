<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Writer\Exception;

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

    /**
     * @param string $tmp_filename
     * @param string $target_filename
     *
     * @return self
     */
    public static function failedOverwrite(string $tmp_filename, string $target_filename): self
    {
        return new self(sprintf(
            'Failed to overwrite file "%s" from temporary file "%s".',
            $target_filename,
            $tmp_filename
        ));
    }

    /**
     * @param string $filename
     *
     * @return static
     */
    public static function notReadable($filename)
    {
        return new static(sprintf('File "%s" is not readable.', $filename));
    }
}
