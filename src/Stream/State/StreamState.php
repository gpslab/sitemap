<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\State;

use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;

/**
 * Service for monitoring the status of the stream.
 */
final class StreamState
{
    private const STATE_CREATED = 0;

    private const STATE_READY = 1;

    private const STATE_CLOSED = 2;

    /**
     * @var int
     */
    private $state = self::STATE_CREATED;

    /**
     * @throws StreamStateException
     */
    public function open(): void
    {
        if ($this->state === self::STATE_READY) {
            throw StreamStateException::alreadyOpened();
        }

        $this->state = self::STATE_READY;
    }

    /**
     * @throws StreamStateException
     */
    public function close(): void
    {
        if ($this->state === self::STATE_CLOSED) {
            throw StreamStateException::alreadyClosed();
        }

        if ($this->state !== self::STATE_READY) {
            throw StreamStateException::notOpened();
        }

        $this->state = self::STATE_CLOSED;
    }

    /**
     * Stream is ready to receive data.
     *
     * @return bool
     */
    public function isReady(): bool
    {
        return $this->state === self::STATE_READY;
    }
}
