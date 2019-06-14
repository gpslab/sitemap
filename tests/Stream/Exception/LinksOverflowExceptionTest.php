<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream\Exception;

use GpsLab\Component\Sitemap\Stream\Exception\LinksOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\OverflowException;

class LinksOverflowExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testWithLimit()
    {
        $exception = LinksOverflowException::withLimit(99);

        $this->assertInstanceOf(LinksOverflowException::class, $exception);
        $this->assertInstanceOf(OverflowException::class, $exception);
        $this->assertEquals('The limit of 99 URLs in the sitemap.xml was exceeded.', $exception->getMessage());
    }
}
