<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream;

use GpsLab\Component\Sitemap\Render\SitemapIndexRender;
use GpsLab\Component\Sitemap\Stream\Exception\FileAccessException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\FileStream;
use GpsLab\Component\Sitemap\Stream\RenderIndexFileStream;
use GpsLab\Component\Sitemap\Url\Url;

class RenderIndexFileStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SitemapIndexRender
     */
    private $render;

    /**
     * @var RenderIndexFileStream
     */
    private $stream;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FileStream
     */
    private $substream;

    /**
     * @var string
     */
    private $expected_content = '';

    /**
     * @var string
     */
    private $host = 'https://example.com/';

    /**
     * @var string
     */
    private $filename = '';

    /**
     * @var string
     */
    private $subfilename = '';

    /**
     * @var int
     */
    private $index = 0;

    protected function setUp()
    {
        if (!$this->filename) {
            $this->filename = tempnam(sys_get_temp_dir(), 'idx');
        }
        if (!$this->subfilename) {
            $this->subfilename = tempnam(sys_get_temp_dir(), 'tsp');
        }
        file_put_contents($this->filename, '');
        file_put_contents($this->subfilename, '');

        $this->render = $this->getMock(SitemapIndexRender::class);
        $this->substream = $this->getMock(FileStream::class);
        $this->stream = new RenderIndexFileStream($this->render, $this->substream, $this->host, $this->filename);
    }

    protected function tearDown()
    {
        $this->assertEquals($this->expected_content, file_get_contents($this->filename));

        unset($this->stream);
        unlink($this->filename);
        if (file_exists($this->subfilename)) {
            unlink($this->subfilename);
        }

        for ($i = 0; $i < $this->index; $i++) {
            $filename = $this->getFilenameOfIndex($i + 1);
            $this->assertFileExists(sys_get_temp_dir().'/'.$filename);
            unlink(sys_get_temp_dir().'/'.$filename);
        }

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
            $this->substream
                ->expects($this->at($i))
                ->method('push')
                ->will($this->returnValue($url->getLoc()))
                ->with($urls[$i])
            ;
        }

        foreach ($urls as $url) {
            $this->stream->push($url);
        }

        $this->assertEquals(count($urls), count($this->stream));

        $this->close();
    }

    public function testNotWritable()
    {
        try {
            $this->stream =  new RenderIndexFileStream($this->render, $this->substream, $this->host, '');
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
        ++$this->index;
        $opened = 'Stream opened';
        $this->render
            ->expects($this->at(0))
            ->method('start')
            ->will($this->returnValue($opened))
        ;
        $this->render
            ->expects($this->at(2))
            ->method('sitemap')
            ->will($this->returnCallback(function ($url, $last_mod) {
                $this->assertInstanceOf(\DateTimeImmutable::class, $last_mod);
                $this->assertEquals($this->host, substr($url, 0, strlen($this->host)));
                $this->assertEquals($this->getFilenameOfIndex($this->index), substr($url, strlen($this->host)));
            }))
        ;

        $this->substream
            ->expects($this->atLeastOnce())
            ->method('open')
        ;
        $this->substream
            ->expects($this->atLeastOnce())
            ->method('getFilename')
            ->will($this->returnValue($this->subfilename))
        ;

        $this->stream->open();
        $this->expected_content .= $opened;
    }

    private function close()
    {
        $closed = 'Stream closed';
        $this->render
            ->expects($this->at(1))
            ->method('end')
            ->will($this->returnValue($closed))
        ;

        $this->substream
            ->expects($this->atLeastOnce())
            ->method('close')
        ;

        $this->stream->close();
        $this->expected_content .= $closed;
    }

    /**
     * @param int $index
     *
     * @return string
     */
    private function getFilenameOfIndex($index)
    {
        // use explode() for correct add index
        // sitemap.xml -> sitemap1.xml
        // sitemap.xml.gz -> sitemap1.xml.gz

        list($filename, $extension) = explode('.', basename($this->subfilename), 2);

        return sprintf('%s%s.%s', $filename, $index, $extension);
    }
}
