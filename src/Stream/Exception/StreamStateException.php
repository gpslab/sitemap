<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\Exception;

class StreamStateException extends \RuntimeException
{
    /**
     * @return static
     */
    final public static function alreadyOpened()
    {
        return new static('Stream is already opened.');
    }

    /**
     * @return static
     */
    final public static function alreadyClosed()
    {
        return new static('Stream is already closed.');
    }

    /**
     * @return static
     */
    final public static function notOpened()
    {
        return new static('Stream not opened.');
    }

    /**
     * @return static
     */
    final public static function notReady()
    {
        return new static('Stream not ready.');
    }

    /**
     * @return static
     */
    final public static function notClosed()
    {
        return new static('Stream not closed.');
    }
}
