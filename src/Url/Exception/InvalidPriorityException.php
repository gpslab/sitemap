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
     * @param string $priority
     *
     * @return self
     */
    public static function invalid(string $priority): self
    {
        return new self(sprintf('You specify invalid priority "%s". Valid values range from 0.0 to 1.0.', $priority));
    }
}
