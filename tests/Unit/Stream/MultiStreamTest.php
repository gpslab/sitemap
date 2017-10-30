<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Unit\Stream;

use GpsLab\Component\Sitemap\Stream\MultiStream;
use GpsLab\Component\Sitemap\Stream\Stream;
use GpsLab\Component\Sitemap\Url\Url;

class MultiStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function streams()
    {
        return [
            [
                [
                    $this->getMock(Stream::class),
                    $this->getMock(Stream::class),
                ],
            ],
            [
                [
                    $this->getMock(Stream::class),
                    $this->getMock(Stream::class),
                    $this->getMock(Stream::class),
                ],
            ],
        ];
    }

    /**
     * @dataProvider streams
     *
     * @param \PHPUnit_Framework_MockObject_MockObject[]|Stream[] $substreams
     */
    public function testOpen(array $substreams)
    {
        $stream = $this->getMultiStream($substreams);

        foreach ($substreams as $substream) {
            $substream
                ->expects($this->once())
                ->method('open')
            ;
        }

        $stream->open();
    }

    /**
     * @dataProvider streams
     *
     * @param \PHPUnit_Framework_MockObject_MockObject[]|Stream[] $substreams
     */
    public function testClose(array $substreams)
    {
        $stream = $this->getMultiStream($substreams);

        foreach ($substreams as $substream) {
            $substream
                ->expects($this->once())
                ->method('close')
            ;
        }

        $stream->close();
    }

    /**
     * @dataProvider streams
     *
     * @param \PHPUnit_Framework_MockObject_MockObject[]|Stream[] $substreams
     */
    public function testPush(array $substreams)
    {
        $urls = [
            new Url('/foo'),
            new Url('/bar'),
            new Url('/baz'),
        ];

        $stream = $this->getMultiStream($substreams);

        foreach ($substreams as $substream) {
            foreach ($urls as $i => $url) {
                $substream
                    ->expects($this->at($i))
                    ->method('push')
                    ->with($url)
                ;
            }
        }

        foreach ($urls as $url) {
            $stream->push($url);
        }

        $this->assertEquals(count($urls), count($stream));
    }

    /**
     * @param Stream[] $substreams
     *
     * @return MultiStream
     */
    private function getMultiStream(array $substreams)
    {
        /* @var $stream MultiStream */
        $stream = (new \ReflectionClass(MultiStream::class))->newInstanceArgs($substreams);

        return $stream;
    }
}
