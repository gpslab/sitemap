<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Writer\Exception;

use GpsLab\Component\Sitemap\Writer\Exception\DeflateCompressionException;
use PHPUnit\Framework\TestCase;

final class DeflateCompressionExceptionTest extends TestCase
{
    public function testFailedAdd(): void
    {
        $exception = DeflateCompressionException::failedInit();

        self::assertInstanceOf(DeflateCompressionException::class, $exception);
        self::assertInstanceOf(\RuntimeException::class, $exception);
        self::assertEquals('Failed init deflate compression.', $exception->getMessage());
    }

    public function testFailedInit(): void
    {
        $exception = DeflateCompressionException::failedAdd('foo');

        self::assertInstanceOf(DeflateCompressionException::class, $exception);
        self::assertInstanceOf(\RuntimeException::class, $exception);
        self::assertEquals('Failed incrementally deflate data "foo".', $exception->getMessage());
    }

    public function testFailedFinish(): void
    {
        $exception = DeflateCompressionException::failedFinish();

        self::assertInstanceOf(DeflateCompressionException::class, $exception);
        self::assertInstanceOf(\RuntimeException::class, $exception);
        self::assertEquals('Failed terminate with the last chunk of data.', $exception->getMessage());
    }
}
