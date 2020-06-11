<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Writer\Exception;

use GpsLab\Component\Sitemap\Writer\Exception\ExtensionNotLoadedException;
use PHPUnit\Framework\TestCase;

final class ExtensionNotLoadedExceptionTest extends TestCase
{
    public function testZlib(): void
    {
        $exception = ExtensionNotLoadedException::zlib();

        self::assertInstanceOf(\RuntimeException::class, $exception);
        self::assertEquals('The Zlib PHP extension is not loaded.', $exception->getMessage());
    }
}
