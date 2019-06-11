<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\Exception;

final class IndexStreamException extends \RuntimeException
{
    /**
     * @param string $filename
     *
     * @return self
     */
    public static function undefinedSubstreamFile(string $filename): self
    {
        return new self(sprintf('Substream file "%s" not exists or not readable.', $filename));
    }

    /**
     * @param string $source
     * @param string $target
     *
     * @return self
     */
    public static function failedRename(string $source, string $target): self
    {
        return new self(sprintf('Failed rename sitemap file "%s" to "%s".', $source, $target));
    }
}
