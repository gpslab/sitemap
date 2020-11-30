<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Writer\Exception;

final class StateException extends \RuntimeException
{
    /**
     * @return self
     */
    public static function alreadyStarted(): self
    {
        return new self('Writing is already started.');
    }

    /**
     * @return self
     */
    public static function notStarted(): self
    {
        return new self('Writing not started.');
    }

    /**
     * @return self
     */
    public static function notReady(): self
    {
        return new self('Writing not ready.');
    }
}
