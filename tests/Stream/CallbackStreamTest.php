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
use GpsLab\Component\Sitemap\Stream\CallbackStream;
use GpsLab\Component\Sitemap\Stream\Exception\LinksOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\SizeOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CallbackStreamTest extends TestCase
{
    /**
     * @var MockObject|SitemapRender
     */
    private $render;

    /**
     * @var CallbackStream
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

    protected function setUp(): void
    {
        $this->render = $this->createMock(SitemapRender::class);
        $call = 0;
        $this->stream = new CallbackStream($this->render, function ($content) use (&$call) {
            if ($call === 0) {
                self::assertEquals(self::OPENED, $content);
            } else {
                self::assertEquals(self::CLOSED, $content);
            }
            ++$call;
        });
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

        $call = 0;
        $this->stream = new CallbackStream($this->render, function ($content) use (&$call, $urls) {
            if ($call === 0) {
                self::assertEquals(self::OPENED, $content);
            } elseif (isset($urls[$call - 1])) {
                self::assertEquals($urls[$call - 1]->getLoc(), $content);
            } else {
                self::assertEquals(self::CLOSED, $content);
            }
            ++$call;
        });

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
                ->with($url)
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
        }

        $this->stream->open();
        foreach ($urls as $url) {
            $this->stream->push($url);
        }
        $this->stream->close();
    }

    public function testOverflowLinks(): void
    {
        $loc = '/';
        $call = 0;
        $this->stream = new CallbackStream($this->render, function ($content) use (&$call, $loc) {
            if ($call === 0) {
                self::assertEquals(self::OPENED, $content);
            } elseif ($call - 1 < CallbackStream::LINKS_LIMIT) {
                self::assertEquals($loc, $content);
            } else {
                self::assertEquals(self::CLOSED, $content);
            }
            ++$call;
        });

        $this->render
            ->expects(self::atLeastOnce())
            ->method('url')
            ->will(self::returnValue($loc))
        ;

        $this->open();
        try {
            for ($i = 0; $i <= CallbackStream::LINKS_LIMIT; ++$i) {
                $this->stream->push(new Url($loc));
            }
            self::assertTrue(false, 'Must throw LinksOverflowException.');
        } catch (LinksOverflowException $e) {
            $this->close();
        }
    }

    public function testOverflowSize(): void
    {
        $i = 0;
        $loops = 10000;
        $loop_size = (int) floor(CallbackStream::BYTE_LIMIT / $loops);
        $prefix_size = CallbackStream::BYTE_LIMIT - ($loops * $loop_size);
        $opened = str_repeat('/', ++$prefix_size); // overflow byte
        $loc = str_repeat('/', $loop_size);

        $this->render
            ->expects(self::once())
            ->method('start')
            ->will(self::returnValue($opened))
        ;
        $this->render
            ->expects(self::atLeastOnce())
            ->method('url')
            ->will(self::returnValue($loc))
        ;
        $call = 0;
        $this->stream = new CallbackStream(
            $this->render,
            function ($content) use (&$call, $loc, &$i, $loops, $opened) {
                if ($call === 0) {
                    self::assertEquals($opened, $content);
                } elseif ($i + 1 < $loops) {
                    self::assertEquals($loc, $content);
                }
                ++$call;
            }
        );

        $this->stream->open();

        try {
            for (; $i < $loops; ++$i) {
                $this->stream->push(new Url($loc));
            }
            self::assertTrue(false, 'Must throw SizeOverflowException.');
        } catch (SizeOverflowException $e) {
            $this->stream->close();
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
    }

    private function close(): void
    {
        $this->render
            ->expects(self::once())
            ->method('end')
            ->will(self::returnValue(self::CLOSED))
        ;
        $this->stream->close();
    }
}
