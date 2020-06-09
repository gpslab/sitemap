<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Writer\Exception;

final class DeflateCompressionException extends \RuntimeException
{
    /**
     * @return self
     */
    public static function failedInit(): self
    {
        return new self('Failed init deflate compression.');
    }

    /**
     * @param string $content
     *
     * @return self
     */
    public static function failedAdd(string $content): self
    {
        return new self(sprintf('Failed incrementally deflate data "%s".', $content));
    }

    /**
     * @return self
     */
    public static function failedFinish(): self
    {
        return new self('Failed terminate with the last chunk of data.');
    }
}
