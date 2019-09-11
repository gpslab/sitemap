<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Writer\State;

use GpsLab\Component\Sitemap\Writer\State\Exception\WriterStateException;

/**
 * Service for monitoring the status of the writing.
 */
final class WriterState
{
    private const STATE_CREATED = 0;

    private const STATE_READY = 1;

    private const STATE_FINISHED = 2;

    /**
     * @var int
     */
    private $state = self::STATE_CREATED;

    public function start(): void
    {
        if ($this->state === self::STATE_READY) {
            throw WriterStateException::alreadyStarted();
        }

        $this->state = self::STATE_READY;
    }

    public function finish(): void
    {
        if ($this->state === self::STATE_FINISHED) {
            throw WriterStateException::alreadyFinished();
        }

        if ($this->state !== self::STATE_READY) {
            throw WriterStateException::notStarted();
        }

        $this->state = self::STATE_FINISHED;
    }

    /**
     * Writer is ready to write content.
     *
     * @return bool
     */
    public function isReady(): bool
    {
        return $this->state === self::STATE_READY;
    }
}
