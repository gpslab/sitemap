<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\Exception;

final class SplitIndexException extends \InvalidArgumentException
{
    /**
     * @param string $pattern
     *
     * @return self
     */
    public static function invalidPartFilenamePattern(string $pattern): self
    {
        return new self(sprintf(
            'The pattern "%s" of index part filename is invalid. '.
            'The pattern should contain a directive like this "/var/www/sitemap%%d.xml"',
            $pattern
        ));
    }

    /**
     * @param string $pattern
     *
     * @return self
     */
    public static function invalidPartWebPathPattern(string $pattern): self
    {
        return new self(sprintf(
            'The pattern "%s" of index part web path is invalid. '.
            'The pattern should contain a directive like this "/sitemap%%d.xml"',
            $pattern
        ));
    }
}
