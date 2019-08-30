<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Exception;

final class InvalidPriorityException extends InvalidArgumentException
{
    /**
     * @param int $priority
     *
     * @return InvalidPriorityException
     */
    public static function invalid(int $priority): self
    {
        return new self(sprintf('You specify invalid priority "%d". Valid values range from 0 to 10.', $priority));
    }
}
