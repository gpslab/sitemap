<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Exception;

use GpsLab\Component\Sitemap\Exception\InvalidArgumentException;

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
