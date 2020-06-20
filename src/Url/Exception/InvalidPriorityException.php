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
    public static function invalidInteger(int $priority): self
    {
        return new self(sprintf('You specify invalid priority "%d". Valid values range from 0 to 10.', $priority));
    }

    /**
     * @param float $priority
     *
     * @return InvalidPriorityException
     */
    public static function invalidFloat(float $priority): self
    {
        return new self(sprintf('You specify invalid priority "%f". Valid values range from 0.0 to 1.0.', $priority));
    }

    /**
     * @param string $priority
     *
     * @return InvalidPriorityException
     */
    public static function invalidString(string $priority): self
    {
        return new self(sprintf('You specify invalid priority "%s". Valid values range from 0.0 to 1.0.', $priority));
    }

    /**
     * @param mixed $priority
     *
     * @return InvalidPriorityException
     */
    public static function unsupportedType($priority): self
    {
        return new self(sprintf(
            'Supported type of priority "string", "float", "int", got "%s" instead.',
            gettype($priority)
        ));
    }
}
