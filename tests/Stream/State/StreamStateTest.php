<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream\State;

use GpsLab\Component\Sitemap\Stream\State\StreamState;
use PHPUnit\Framework\TestCase;

class StreamStateTest extends TestCase
{
    /**
     * @var StreamState
     */
    private $state;

    protected function setUp()
    {
        $this->state = new StreamState();
    }

    protected function tearDown()
    {
        if ($this->state->isReady()) {
            $this->state->close();
        }
    }

    /**
     * @expectedException \GpsLab\Component\Sitemap\Stream\Exception\StreamStateException
     */
    public function testAlreadyOpened()
    {
        $this->assertFalse($this->state->isReady());
        $this->state->open();
        $this->assertTrue($this->state->isReady());

        // already opened
        $this->state->open();
    }

    /**
     * @expectedException \GpsLab\Component\Sitemap\Stream\Exception\StreamStateException
     */
    public function testAlreadyClosed()
    {
        $this->assertFalse($this->state->isReady());
        $this->state->open();
        $this->assertTrue($this->state->isReady());
        $this->state->close();
        $this->assertFalse($this->state->isReady());

        // already closed
        $this->state->close();
    }

    /**
     * @expectedException \GpsLab\Component\Sitemap\Stream\Exception\StreamStateException
     */
    public function testNotOpened()
    {
        $this->assertFalse($this->state->isReady());

        // not opened
        $this->state->close();
    }

    /**
     * @expectedException \GpsLab\Component\Sitemap\Stream\Exception\StreamStateException
     */
    public function testNotClosed()
    {
        $state = new StreamState();
        $state->open();
        unset($state);
    }

    public function testAllIsGood()
    {
        $state = new StreamState();
        $this->assertFalse($state->isReady());
        $state->open();
        $this->assertTrue($state->isReady());
        $state->close();
        $this->assertFalse($state->isReady());
        unset($state);
    }
}
