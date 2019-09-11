<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Writer\Exception;

use GpsLab\Component\Sitemap\Writer\Exception\FileAccessException;
use PHPUnit\Framework\TestCase;

class FileAccessExceptionTest extends TestCase
{
    public function testNotWritable(): void
    {
        $exception = FileAccessException::notWritable('foo');

        self::assertInstanceOf(FileAccessException::class, $exception);
        self::assertInstanceOf(\RuntimeException::class, $exception);
        self::assertEquals('File "foo" is not writable.', $exception->getMessage());
    }

    public function testFailedOverwrite(): void
    {
        $exception = FileAccessException::failedOverwrite('foo', 'bar');

        self::assertInstanceOf(FileAccessException::class, $exception);
        self::assertInstanceOf(\RuntimeException::class, $exception);
        self::assertEquals('Failed to overwrite file "bar" from temporary file "foo".', $exception->getMessage());
    }

    public function testNotReadable(): void
    {
        $exception = FileAccessException::notReadable('foo');

        self::assertInstanceOf(FileAccessException::class, $exception);
        self::assertInstanceOf(\RuntimeException::class, $exception);
        self::assertEquals('File "foo" is not readable.', $exception->getMessage());
    }
}
