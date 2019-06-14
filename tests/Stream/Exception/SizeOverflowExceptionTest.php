<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream\Exception;

use GpsLab\Component\Sitemap\Stream\Exception\SizeOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\OverflowException;

class SizeOverflowExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testWithLimit()
    {
        $exception = SizeOverflowException::withLimit(99);

        $this->assertInstanceOf(SizeOverflowException::class, $exception);
        $this->assertInstanceOf(OverflowException::class, $exception);
        $this->assertEquals('The limit of 99 byte in the sitemap.xml was exceeded.', $exception->getMessage());
    }
}
