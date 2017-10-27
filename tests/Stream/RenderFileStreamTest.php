<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream;

use GpsLab\Component\Sitemap\Render\SitemapRender;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\RenderFileStream;
use GpsLab\Component\Sitemap\Url\Url;

class RenderFileStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SitemapRender
     */
    private $render;

    /**
     * @var RenderFileStream
     */
    private $stream;

    /**
     * @var string
     */
    private $expected_content = '';

    /**
     * @var string
     */
    private $filename = '';

    /**
     * @var string
     */
    private $opened = 'Stream opened';

    /**
     * @var string
     */
    private $closed = 'Stream closed';

    protected function setUp()
    {
        if (!$this->filename) {
            $this->filename = tempnam(sys_get_temp_dir(), 'test');
        }
        file_put_contents($this->filename, '');

        $this->render = $this->getMock(SitemapRender::class);
        $this->stream = new RenderFileStream($this->render, $this->filename);
    }

    protected function tearDown()
    {
        $this->assertEquals($this->expected_content, file_get_contents($this->filename));

        unlink($this->filename);
        $this->expected_content = '';
    }

    public function testGetFilename()
    {
        $this->assertEquals($this->filename, $this->stream->getFilename());
    }

    public function testOpenClose()
    {
        $this->open();
        $this->close();
    }

    public function testAlreadyOpened()
    {
        $this->open();

        try {
            $this->stream->open();
            $this->assertTrue(false, 'Must throw StreamStateException.');
        } catch (StreamStateException $e) {
            $this->close();
        }
    }

    /**
     * @expectedException \GpsLab\Component\Sitemap\Stream\Exception\StreamStateException
     */
    public function testNotOpened()
    {
        $this->render
            ->expects($this->never())
            ->method('end')
        ;

        $this->stream->close();
    }

    public function testAlreadyClosed()
    {
        $this->open();
        $this->close();

        try {
            $this->stream->close();
            $this->assertTrue(false, 'Must throw StreamStateException.');
        } catch (StreamStateException $e) {
        }
    }

    /**
     * @expectedException \GpsLab\Component\Sitemap\Stream\Exception\StreamStateException
     */
    public function testPushNotOpened()
    {
        $this->stream->push(new Url('/'));
    }

    /**
     * @expectedException \GpsLab\Component\Sitemap\Stream\Exception\StreamStateException
     */
    public function testPushClosed()
    {
        $this->open();
        $this->close();

        $this->stream->push(new Url('/'));
    }

    public function testPush()
    {
        $this->open();

        $urls = [
            new Url('/foo'),
            new Url('/bar'),
            new Url('/baz'),
        ];

        foreach ($urls as $i => $url) {
            /* @var $url Url */
            $this->render
                ->expects($this->at($i))
                ->method('url')
                ->will($this->returnValue($url->getLoc()))
                ->with($urls[$i])
            ;
            $this->expected_content .= $url->getLoc();
        }

        foreach ($urls as $url) {
            $this->stream->push($url);
        }

        $this->assertEquals(count($urls), count($this->stream));

        $this->close();
    }

    private function open()
    {
        $this->render
            ->expects($this->at(0))
            ->method('start')
            ->will($this->returnValue($this->opened))
        ;
        $this->render
            ->expects($this->at(1))
            ->method('end')
            ->will($this->returnValue($this->closed))
        ;

        $this->stream->open();
        $this->expected_content .= $this->opened;
    }

    private function close()
    {
        $this->stream->close();
        $this->expected_content .= $this->closed;
    }
}
