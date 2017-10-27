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
use GpsLab\Component\Sitemap\Stream\Exception\FileAccessException;
use GpsLab\Component\Sitemap\Stream\Exception\LinksOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\RenderBzip2FileStream;
use GpsLab\Component\Sitemap\Url\Url;

class RenderBzip2FileStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SitemapRender
     */
    private $render;

    /**
     * @var RenderBzip2FileStream
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
            $this->filename = tempnam(sys_get_temp_dir(), 'sitemap');
        }
        file_put_contents($this->filename, '');

        $this->render = $this->getMock(SitemapRender::class);
        $this->stream = new RenderBzip2FileStream($this->render, $this->filename);
    }

    protected function tearDown()
    {
        $this->assertEquals($this->expected_content, $this->getContent());

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
            for ($i = 0; $i <= RenderBzip2FileStream::LINKS_LIMIT; ++$i) {
                $this->stream->push(new Url($loc));
            }
            $this->assertTrue(false, 'Must throw LinksOverflowException.');
        } catch (LinksOverflowException $e) {
            $this->stream->close();
            file_put_contents($this->filename, ''); // not check content
        }
    }

    public function testNotWritable()
    {
        try {
            $this->stream = new RenderBzip2FileStream($this->render, '');
            $this->stream->open();
            $this->assertTrue(false, 'Must throw FileAccessException.');
        } catch (FileAccessException $e) {
            try {
                unset($this->stream);
            } catch (StreamStateException $e) {
                // impossible correct close stream because it is incorrect opened
            }
        }
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

    /**
     * @return string
     */
    private function getContent()
    {
        $content = '';
        $handle = bzopen($this->filename, 'r');
        while (!feof($handle)) {
            $content .= fread($handle, 1024);
        }
        bzclose($handle);

        return $content;
    }
}
