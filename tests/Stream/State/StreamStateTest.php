<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream\State;

use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\State\StreamState;
use PHPUnit\Framework\TestCase;

final class StreamStateTest extends TestCase
{
    /**
     * @var StreamState
     */
    private $state;

    protected function setUp(): void
    {
        $this->state = new StreamState();
    }

    protected function tearDown(): void
    {
        if ($this->state->isReady()) {
            $this->state->close();
        }
    }

    public function testAlreadyOpened(): void
    {
        $this->expectException(StreamStateException::class);
        self::assertFalse($this->state->isReady());
        $this->state->open();
        self::assertTrue($this->state->isReady());

        // already opened
        $this->state->open();
    }

    public function testAlreadyClosed(): void
    {
        $this->expectException(StreamStateException::class);
        self::assertFalse($this->state->isReady());
        $this->state->open();
        self::assertTrue($this->state->isReady());
        $this->state->close();
        self::assertFalse($this->state->isReady());

        // already closed
        $this->state->close();
    }

    public function testNotOpened(): void
    {
        $this->expectException(StreamStateException::class);
        self::assertFalse($this->state->isReady());

        // not opened
        $this->state->close();
    }

    public function testAllIsGood(): void
    {
        $state = new StreamState();
        self::assertFalse($state->isReady());
        $state->open();
        self::assertTrue($state->isReady());
        $state->close();
        self::assertFalse($state->isReady());
        unset($state);
    }
}
