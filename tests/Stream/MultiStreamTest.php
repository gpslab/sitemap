<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream;

use GpsLab\Component\Sitemap\Stream\MultiStream;
use GpsLab\Component\Sitemap\Stream\Stream;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MultiStreamTest extends TestCase
{
    /**
     * @return MockObject[][][]&Stream[][][]
     */
    public function getStreams(): array
    {
        return [
            [
                [
                    $this->createMock(Stream::class),
                ],
            ],
            [
                [
                    $this->createMock(Stream::class),
                    $this->createMock(Stream::class),
                ],
            ],
            [
                [
                    $this->createMock(Stream::class),
                    $this->createMock(Stream::class),
                    $this->createMock(Stream::class),
                ],
            ],
        ];
    }

    /**
     * @dataProvider getStreams
     *
     * @param MockObject[]&Stream[] $substreams
     */
    public function testOpen(array $substreams): void
    {
        $i = 0;
        $stream = new MultiStream(...$substreams);

        foreach ($substreams as $substream) {
            $substream
                ->expects(self::once())
                ->method('open')
                ->willReturnCallback(static function () use (&$i) {
                    ++$i;
                })
            ;
        }

        $stream->open();

        self::assertEquals(count($substreams), $i);
    }

    /**
     * @dataProvider getStreams
     *
     * @param MockObject[]&Stream[] $substreams
     */
    public function testClose(array $substreams): void
    {
        $i = 0;
        $stream = new MultiStream(...$substreams);

        foreach ($substreams as $substream) {
            $substream
                ->expects(self::once())
                ->method('close')
                ->willReturnCallback(static function () use (&$i) {
                    ++$i;
                })
            ;
        }

        $stream->close();

        self::assertEquals(count($substreams), $i);
    }

    /**
     * @dataProvider getStreams
     *
     * @param MockObject[]&Stream[] $substreams
     */
    public function testPush(array $substreams): void
    {
        $i = 0;
        $urls = [
            new Url('/foo'),
            new Url('/bar'),
            new Url('/baz'),
        ];

        $stream = new MultiStream(...$substreams);

        foreach ($substreams as $substream) {
            foreach ($urls as $j => $url) {
                $substream
                    ->expects(self::at($j))
                    ->method('push')
                    ->with($url)
                    ->willReturnCallback(static function () use (&$i) {
                        ++$i;
                    })
                ;
            }
        }

        foreach ($urls as $url) {
            $stream->push($url);
        }

        self::assertEquals(count($substreams) * count($urls), $i);
    }

    /**
     * @dataProvider getStreams
     *
     * @param MockObject[]&Stream[] $substreams
     */
    public function testReset(array $substreams): void
    {
        $i = 0;
        $url = new Url('/foo');

        $stream = new MultiStream(...$substreams);
        foreach ($substreams as $substream) {
            $substream
                ->expects(self::at(0))
                ->method('push')
                ->with($url)
                ->willReturnCallback(static function () use (&$i) {
                    ++$i;
                })
            ;
        }
        $stream->push($url);

        $stream->close();

        self::assertEquals(count($substreams), $i);
    }

    public function testEmptyStream(): void
    {
        /* @var $url Url&MockObject */
        $url = $this->createMock(Url::class);
        $url->expects(self::never())->method('getLocation');
        $url->expects(self::never())->method('getLastModify');
        $url->expects(self::never())->method('getChangeFrequency');
        $url->expects(self::never())->method('getPriority');

        $stream = new MultiStream();

        // do nothing
        $stream->open();
        $stream->push($url);
        $stream->close();
    }
}
