<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream;

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
    private const OPENED = 'Stream opened';

    /**
     * @var string
     */
    private const CLOSED = 'Stream closed';

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
        self::assertEquals($this->expected_buffer, ob_get_clean());
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
        $this->open();

        try {
            $this->stream->open();
            self::assertTrue(false, 'Must throw StreamStateException.');
        } catch (StreamStateException $e) {
            $this->close();
        }
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
        $this->expectException(StreamStateException::class);
        $this->open();
        $this->close();

        $this->stream->close();
    }

    public function testPushNotOpened(): void
    {
        $this->expectException(StreamStateException::class);
        $this->stream->push(new Url('/'));
    }

    public function testPushClosed(): void
    {
        $this->expectException(StreamStateException::class);
        $this->open();
        $this->close();

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
            ->will(self::returnValue(self::OPENED))
        ;
        foreach ($urls as $i => $url) {
            /* @var $url Url */
            $this->render
                ->expects(self::at($render_call++))
                ->method('url')
                ->with($urls[$i])
                ->will(self::returnValue($url->getLoc()))
            ;
            // render end string after first url
            if ($i === 0) {
                $this->render
                    ->expects(self::at($render_call++))
                    ->method('end')
                    ->will(self::returnValue(self::CLOSED))
                ;
            }
            $this->expected_buffer .= $url->getLoc();
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
        $loc = '/';
        $this->stream->open();
        $this->render
            ->expects(self::atLeastOnce())
            ->method('url')
            ->will(self::returnValue($loc))
        ;

        try {
            for ($i = 0; $i <= OutputStream::LINKS_LIMIT; ++$i) {
                $this->stream->push(new Url($loc));
            }
            self::assertTrue(false, 'Must throw LinksOverflowException.');
        } catch (LinksOverflowException $e) {
            $this->stream->close();
            ob_clean(); // not check content
        }
    }

    public function testOverflowSize(): void
    {
        $loops = 10000;
        $loop_size = (int) floor(OutputStream::BYTE_LIMIT / $loops);
        $prefix_size = OutputStream::BYTE_LIMIT - ($loops * $loop_size);
        ++$prefix_size; // overflow byte
        $loc = str_repeat('/', $loop_size);

        $this->render
            ->expects(self::once())
            ->method('start')
            ->will(self::returnValue(str_repeat('/', $prefix_size)))
        ;
        $this->render
            ->expects(self::atLeastOnce())
            ->method('url')
            ->will(self::returnValue($loc))
        ;

        $this->stream->open();

        try {
            for ($i = 0; $i < $loops; ++$i) {
                $this->stream->push(new Url($loc));
            }
            self::assertTrue(false, 'Must throw SizeOverflowException.');
        } catch (SizeOverflowException $e) {
            $this->stream->close();
            ob_clean(); // not check content
        }
    }

    private function open(): void
    {
        $this->render
            ->expects(self::once())
            ->method('start')
            ->will(self::returnValue(self::OPENED))
        ;
        $this->stream->open();
        $this->expected_buffer .= self::OPENED;
    }

    private function close(): void
    {
        $this->render
            ->expects(self::once())
            ->method('end')
            ->will(self::returnValue(self::CLOSED))
        ;
        $this->stream->close();
        $this->expected_buffer .= self::CLOSED;
    }
}
