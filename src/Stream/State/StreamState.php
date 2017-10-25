<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\State;

use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;

class StreamState
{
    const STATE_CREATED = 0;
    const STATE_READY = 1;
    const STATE_CLOSED = 2;

    /**
     * @var int
     */
    public $state = self::STATE_CREATED;

    public function open()
    {
        if ($this->state == self::STATE_READY) {
            throw StreamStateException::alreadyOpened();
        }

        $this->state = self::STATE_READY;
    }

    public function close()
    {
        if ($this->state == self::STATE_CLOSED) {
            throw StreamStateException::alreadyClosed();
        }

        if ($this->state != self::STATE_READY) {
            throw StreamStateException::notOpened();
        }

        $this->state = self::STATE_CLOSED;
    }

    /**
     * Stream is ready to receive data.
     *
     * @return bool
     */
    public function isReady()
    {
        return $this->state == self::STATE_READY;
    }

    /**
     * Did you not forget to close the stream?
     */
    public function __destruct()
    {
        if ($this->state == self::STATE_READY) {
            throw StreamStateException::notClosed();
        }
    }
}
