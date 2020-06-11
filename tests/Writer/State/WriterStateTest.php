<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Writer\State;

use GpsLab\Component\Sitemap\Writer\State\Exception\WriterStateException;
use GpsLab\Component\Sitemap\Writer\State\WriterState;
use PHPUnit\Framework\TestCase;

final class WriterStateTest extends TestCase
{
    /**
     * @var WriterState
     */
    private $state;

    protected function setUp(): void
    {
        $this->state = new WriterState();
    }

    protected function tearDown(): void
    {
        if ($this->state->isReady()) {
            $this->state->finish();
        }
    }

    public function testAlreadyOpened(): void
    {
        $this->expectException(WriterStateException::class);
        self::assertFalse($this->state->isReady());
        $this->state->start();
        self::assertTrue($this->state->isReady());

        // already started
        $this->state->start();
    }

    public function testAlreadyClosed(): void
    {
        $this->expectException(WriterStateException::class);
        self::assertFalse($this->state->isReady());
        $this->state->start();
        self::assertTrue($this->state->isReady());
        $this->state->finish();
        self::assertFalse($this->state->isReady());

        // already finished
        $this->state->finish();
    }

    public function testNotOpened(): void
    {
        $this->expectException(WriterStateException::class);
        self::assertFalse($this->state->isReady());

        // not started
        $this->state->finish();
    }

    public function testAllIsGood(): void
    {
        $state = new WriterState();
        self::assertFalse($state->isReady());
        $state->start();
        self::assertTrue($state->isReady());
        $state->finish();
        self::assertFalse($state->isReady());
        unset($state);
    }
}
