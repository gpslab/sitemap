<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream\Exception;

use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;

class StreamStateExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testAlreadyOpened()
    {
        $exception = StreamStateException::alreadyOpened();

        $this->assertInstanceOf(StreamStateException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('Stream is already opened.', $exception->getMessage());
    }

    public function testAlreadyClosed()
    {
        $exception = StreamStateException::alreadyClosed();

        $this->assertInstanceOf(StreamStateException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('Stream is already closed.', $exception->getMessage());
    }

    public function testNotOpened()
    {
        $exception = StreamStateException::notOpened();

        $this->assertInstanceOf(StreamStateException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('Stream not opened.', $exception->getMessage());
    }

    public function testNotReady()
    {
        $exception = StreamStateException::notReady();

        $this->assertInstanceOf(StreamStateException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('Stream not ready.', $exception->getMessage());
    }

    public function testNotClosed()
    {
        $exception = StreamStateException::notClosed();

        $this->assertInstanceOf(StreamStateException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('Stream not closed.', $exception->getMessage());
    }
}
