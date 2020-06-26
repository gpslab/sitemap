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
use GpsLab\Component\Sitemap\Stream\RenderFileStream;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\TestCase;

class RenderFileStreamTest extends TestCase
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
        try {
            $this->stream->close();
        } catch (StreamStateException $e) {
            // already closed exception is correct error
            // test correct saved content
            self::assertEquals($this->expected_content, file_get_contents($this->filename));
        }

        $this->stream = null;
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

    /**
     * @expectedException \GpsLab\Component\Sitemap\Stream\Exception\StreamStateException
     */
    public function testAlreadyOpened()
    {
        $this->stream->open();
        $this->stream->open();
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
            $this->expected_content .= $url->getLoc();
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
            for ($i = 0; $i <= RenderFileStream::LINKS_LIMIT; ++$i) {
                $this->stream->push(new Url($loc));
            }
            $this->assertTrue(false, 'Must throw LinksOverflowException.');
        } catch (LinksOverflowException $e) {
            $this->stream->close();
            file_put_contents($this->filename, ''); // not check content
        }
    }

    public function testOverflowSize()
    {
        $loops = 10000;
        $loop_size = (int) floor(RenderFileStream::BYTE_LIMIT / $loops);
        $prefix_size = RenderFileStream::BYTE_LIMIT - ($loops * $loop_size);
        ++$prefix_size; // overflow byte
        $loc = str_repeat('/', $loop_size);

        $url = $this
            ->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $url
            ->expects($this->atLeastOnce())
            ->method('getLoc')
            ->willReturn($loc)
        ;

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
                $this->stream->push($url);
            }
            $this->assertTrue(false, 'Must throw SizeOverflowException.');
        } catch (SizeOverflowException $e) {
            $this->stream->close();
            file_put_contents($this->filename, ''); // not check content
        }
    }

    public function testReset()
    {
        $this->open();
        $this->stream->push(new Url('/'));
        $this->assertCount(1, $this->stream);
        $this->close();
        $this->assertCount(0, $this->stream);
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
