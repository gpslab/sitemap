<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream;

use GpsLab\Component\Compressor\CompressorInterface;
use GpsLab\Component\Sitemap\Stream\CompressFileStream;
use GpsLab\Component\Sitemap\Stream\FileStream;
use GpsLab\Component\Sitemap\Url\Url;

class CompressFileStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CompressFileStream
     */
    private $stream;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FileStream
     */
    private $substream;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CompressorInterface
     */
    private $compressor;

    /**
     * @var string
     */
    private $filename = 'sitemap.xml.gz';

    protected function setUp()
    {
        $this->substream = $this->getMock(FileStream::class);
        $this->compressor = $this->getMock(CompressorInterface::class);

        $this->stream = new CompressFileStream($this->substream, $this->compressor, $this->filename);
    }

    public function testGetFilename()
    {
        $this->assertEquals($this->filename, $this->stream->getFilename());
    }

    public function testOpen()
    {
        $this->substream
            ->expects($this->once())
            ->method('open')
        ;

        $this->stream->open();
    }

    public function testClose()
    {
        $filename = 'sitemap.xml';

        $this->substream
            ->expects($this->once())
            ->method('close')
        ;
        $this->substream
            ->expects($this->once())
            ->method('getFilename')
            ->will($this->returnValue($filename))
        ;

        $this->compressor
            ->expects($this->once())
            ->method('compress')
            ->with($filename, $this->filename)
        ;

        $this->stream->close();
    }

    public function testPush()
    {
        $url = new Url('/');

        $this->substream
            ->expects($this->once())
            ->method('push')
            ->with($url)
        ;

        $this->stream->push($url);
    }

    public function testCount()
    {
        $counter = 100;

        $this->substream
            ->expects($this->once())
            ->method('count')
            ->will($this->returnValue($counter))
        ;

        $this->assertEquals($counter, $this->stream->count());
    }
}
