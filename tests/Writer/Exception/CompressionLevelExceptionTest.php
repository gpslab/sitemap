<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Writer\Exception;

use GpsLab\Component\Sitemap\Writer\Exception\CompressionLevelException;
use PHPUnit\Framework\TestCase;

final class CompressionLevelExceptionTest extends TestCase
{
    public function testInvalid(): void
    {
        $exception = CompressionLevelException::invalid('foo', 0, 10);

        self::assertInstanceOf(\InvalidArgumentException::class, $exception);
        self::assertEquals('The compression level "foo" must be in interval [0, 10].', $exception->getMessage());
    }
}
