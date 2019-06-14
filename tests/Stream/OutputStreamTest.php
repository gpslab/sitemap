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
use GpsLab\Component\Sitemap\Stream\Exception\LinksOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\SizeOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\OutputStream;
use GpsLab\Component\Sitemap\Url\Url;

class OutputStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SitemapRender
     */
    private $render;

    /**
     * @var OutputStream
     */
    private $stream;

    /**
     * @var string
     */
    private $opened = 'Stream opened';

    /**
     * @var string
     */
    private $closed = 'Stream closed';

    /**
     * @var string
     */
    private $expected_buffer = '';

    protected function setUp()
    {
        $this->render = $this->getMock(SitemapRender::class);

        $this->stream = new OutputStream($this->render);
        ob_start();
    }

    protected function tearDown()
    {
        $this->assertEquals($this->expected_buffer, ob_get_clean());
        $this->expected_buffer = '';
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

    /**
     * @expectedException \GpsLab\Component\Sitemap\Stream\Exception\StreamStateException
     */
    public function testAlreadyClosed()
    {
        $this->open();
        $this->close();

        $this->stream->close();
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
            $this->expected_buffer .= $url->getLoc();
        }

        foreach ($urls as $url) {
            $this->stream->push($url);
        }

        $this->assertEquals(count($urls), count($this->stream));

        $this->close();
    }

    public function testOverflowLinks()
    {
        $loc = '/';
        $this->stream->open();
        $this->render
            ->expects($this->atLeastOnce())
            ->method('url')
            ->will($this->returnValue($loc))
        ;

        try {
            for ($i = 0; $i <= OutputStream::LINKS_LIMIT; ++$i) {
                $this->stream->push(new Url($loc));
            }
            $this->assertTrue(false, 'Must throw LinksOverflowException.');
        } catch (LinksOverflowException $e) {
            $this->stream->close();
            ob_clean(); // not check content
        }
    }

    public function testOverflowSize()
    {
        $loops = 10000;
        $loop_size = (int) floor(OutputStream::BYTE_LIMIT / $loops);
        $prefix_size = OutputStream::BYTE_LIMIT - ($loops * $loop_size);
        $prefix_size += 1; // overflow byte
        $loc = str_repeat('/', $loop_size);

        $this->render
            ->expects($this->at(0))
            ->method('start')
            ->will($this->returnValue(str_repeat('/', $prefix_size)))
        ;
        $this->render
            ->expects($this->atLeastOnce())
            ->method('url')
            ->will($this->returnValue($loc))
        ;

        $this->stream->open();

        try {
            for ($i = 0; $i < $loops; ++$i) {
                $this->stream->push(new Url($loc));
            }
            $this->assertTrue(false, 'Must throw SizeOverflowException.');
        } catch (SizeOverflowException $e) {
            $this->stream->close();
            ob_clean(); // not check content
        }
    }

    public function testReset()
    {
        $this->open();
        $this->stream->push(new Url('/'));
        $this->assertEquals(1, count($this->stream));
        $this->close();
        $this->assertEquals(0, count($this->stream));
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
        $this->expected_buffer .= $this->opened;
    }

    private function close()
    {
        $this->stream->close();
        $this->expected_buffer .= $this->closed;
    }
}
