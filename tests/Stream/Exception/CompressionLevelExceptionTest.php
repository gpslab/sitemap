<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream\Exception;

use GpsLab\Component\Sitemap\Stream\Exception\CompressionLevelException;

class CompressionLevelExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalid()
    {
        $exception = CompressionLevelException::invalid(-1, 2, 22);

        $this->assertInstanceOf(CompressionLevelException::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertEquals('Compression level "-1" must be in interval [2, 22].', $exception->getMessage());
    }
}
