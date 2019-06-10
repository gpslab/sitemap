<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\Exception;

final class StreamStateException extends \RuntimeException
{
    /**
     * @return self
     */
    public static function alreadyOpened(): self
    {
        return new self('Stream is already opened.');
    }

    /**
     * @return self
     */
    public static function alreadyClosed(): self
    {
        return new self('Stream is already closed.');
    }

    /**
     * @return self
     */
    public static function notOpened(): self
    {
        return new self('Stream not opened.');
    }

    /**
     * @return self
     */
    public static function notReady(): self
    {
        return new self('Stream not ready.');
    }

    /**
     * @return self
     */
    public static function notClosed(): self
    {
        return new self('Stream not closed.');
    }
}
