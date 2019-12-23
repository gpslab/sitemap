<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream\Exception;

use GpsLab\Component\Sitemap\Stream\Exception\FileAccessException;
use PHPUnit\Framework\TestCase;

class FileAccessExceptionTest extends TestCase
{
    public function testNotWritable()
    {
        $exception = FileAccessException::notWritable('/foo.xml');

        $this->assertInstanceOf(FileAccessException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('File "/foo.xml" is not writable.', $exception->getMessage());
    }

    public function testNotReadable()
    {
        $exception = FileAccessException::notReadable('/foo.xml');

        $this->assertInstanceOf(FileAccessException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals('File "/foo.xml" is not readable.', $exception->getMessage());
    }

    public function testFailedOverwrite()
    {
        $exception = FileAccessException::failedOverwrite('/tmp/foo.xml.tmp', '/bar.xml');
        $message = 'Failed to overwrite file "/bar.xml" from temporary file "/tmp/foo.xml.tmp".';

        $this->assertInstanceOf(FileAccessException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }
}
