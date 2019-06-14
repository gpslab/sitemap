<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\Exception;

class IndexStreamException extends \RuntimeException
{
    /**
     * @param string $source
     * @param string $target
     *
     * @return self
     */
    public static function failedRename($source, $target)
    {
        return new self(sprintf('Failed rename sitemap file "%s" to "%s".', $source, $target));
    }
}
