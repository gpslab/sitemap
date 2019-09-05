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

final class SplitIndexException extends \InvalidArgumentException
{
    /**
     * @param string $pattern
     *
     * @return SplitIndexException
     */
    public static function invalidPartFilenamePattern(string $pattern): self
    {
        return new self(sprintf(
            'The pattern "%s" of index part filename is invalid. '.
            'The pattern should contain a directive like this "sitemap%%d.xml"',
            $pattern
        ));
    }
}
