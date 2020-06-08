<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream;

use GpsLab\Component\Sitemap\Limiter;
use GpsLab\Component\Sitemap\Render\SitemapRender;
use GpsLab\Component\Sitemap\Stream\Exception\LinksOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\SizeOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\OutputStream;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OutputStreamTest extends TestCase
{
    /**
     * @var string
     */
    private const OPENED = 'Stream opened';

    /**
     * @var string
     */
    private const CLOSED = 'Stream closed';

    /**
     * @var MockObject|SitemapRender
     */
    private $render;

    /**
     * @var OutputStream
     */
    private $stream;

    /**
     * @var string
     */
    private $expected_buffer = '';

    protected function setUp(): void
    {
        $this->render = $this->createMock(SitemapRender::class);

        $this->stream = new OutputStream($this->render);
        ob_start();
    }

    protected function tearDown(): void
    {
        if ($this->expected_buffer) {
            self::assertEquals($this->expected_buffer, ob_get_clean());
        } else {
            // not need check buffer
            // get buffer only for fix Risk by PHPUnit
            ob_get_clean();
        }
        $this->expected_buffer = '';
        ob_clean();
    }

    public function testOpenClose(): void
    {
        $this->open();
        $this->close();
    }

    public function testAlreadyOpened(): void
    {
        $this->stream->open();
        $this->expectException(StreamStateException::class);
        $this->stream->open();
    }

    public function testNotOpened(): void
    {
        $this->expectException(StreamStateException::class);
        $this->render
            ->expects(self::never())
            ->method('end')
        ;

        $this->stream->close();
    }

    public function testAlreadyClosed(): void
    {
        $this->open();
        $this->close();

        $this->expectException(StreamStateException::class);
        $this->stream->close();
    }

    public function testPushNotOpened(): void
    {
        $this->expectException(StreamStateException::class);
        $this->stream->push(new Url('/'));
    }

    public function testPushClosed(): void
    {
        $this->open();
        $this->close();

        $this->expectException(StreamStateException::class);
        $this->stream->push(new Url('/'));
    }

    public function testPush(): void
    {
        $urls = [
            new Url('/foo'),
            new Url('/bar'),
            new Url('/baz'),
        ];

        $this->expected_buffer .= self::OPENED;
        $render_call = 0;
        $this->render
            ->expects(self::at($render_call++))
            ->method('start')
            ->willReturn(self::OPENED)
        ;
        $this->render
            ->expects(self::at($render_call++))
            ->method('end')
            ->willReturn(self::CLOSED)
        ;
        foreach ($urls as $i => $url) {
            /* @var $url Url */
            $this->render
                ->expects(self::at($render_call++))
                ->method('url')
                ->with($urls[$i])
                ->willReturn($url->getLocation())
            ;
            $this->expected_buffer .= $url->getLocation();
        }
        $this->expected_buffer .= self::CLOSED;

        $this->stream->open();
        foreach ($urls as $url) {
            $this->stream->push($url);
        }

        $this->stream->close();
    }

    public function testOverflowLinks(): void
    {
        $url = new Url('/');
        $this->stream->open();
        $this->render
            ->expects(self::atLeastOnce())
            ->method('url')
            ->willReturn($url->getLocation())
        ;

        for ($i = 0; $i < Limiter::LINKS_LIMIT; ++$i) {
            $this->stream->push($url);
        }

        $this->expectException(LinksOverflowException::class);
        $this->stream->push($url);
    }

    public function testOverflowSize(): void
    {
        $loops = 10000;
        $loop_size = (int) floor(Limiter::BYTE_LIMIT / $loops);
        $prefix_size = Limiter::BYTE_LIMIT - ($loops * $loop_size);
        ++$prefix_size; // overflow byte
        $loc = str_repeat('/', $loop_size);
        $url = new Url($loc);

        $this->render
            ->expects(self::once())
            ->method('start')
            ->willReturn(str_repeat('/', $prefix_size))
        ;
        $this->render
            ->expects(self::atLeastOnce())
            ->method('url')
            ->willReturn($loc)
        ;

        $this->stream->open();

        $this->expectException(SizeOverflowException::class);
        for ($i = 0; $i < $loops; ++$i) {
            $this->stream->push($url);
        }
    }

    private function open(): void
    {
        $this->render
            ->expects(self::once())
            ->method('start')
            ->willReturn(self::OPENED)
        ;
        $this->render
            ->expects(self::once())
            ->method('end')
            ->willReturn(self::CLOSED)
        ;
        $this->stream->open();
        $this->expected_buffer .= self::OPENED;
    }

    private function close(): void
    {
        $this->stream->close();
        $this->expected_buffer .= self::CLOSED;
    }
}
