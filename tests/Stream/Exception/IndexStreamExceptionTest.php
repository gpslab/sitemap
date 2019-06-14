<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream\Exception;

use GpsLab\Component\Sitemap\Stream\Exception\IndexStreamException;

class IndexStreamExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testFailedOverwrite()
    {
        $exception = IndexStreamException::failedRename('/tmp/foo.xml.tmp', '/bar.xml');
        $message = 'Failed rename sitemap file "/tmp/foo.xml.tmp" to "/bar.xml".';

        $this->assertInstanceOf(IndexStreamException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }
}
