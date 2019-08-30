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
     * @param float $priority
     *
     * @return InvalidPriorityException
     */
    public static function invalid(float $priority): self
    {
        return new self(sprintf('You specify invalid priority "%f". Valid values range from 0.0 to 1.0.', $priority));
    }
}
