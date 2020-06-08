<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Writer\Exception;

final class CompressionEncodingException extends InvalidCompressionArgumentException
{
    /**
     * @param mixed $encoding
     *
     * @return self
     */
    public static function invalid($encoding): self
    {
        return new self(sprintf('The compression encoding "%s" is invalid, must be ZLIB_ENCODING_*.', $encoding));
    }
}
