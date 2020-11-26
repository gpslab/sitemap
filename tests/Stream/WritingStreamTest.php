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
use GpsLab\Component\Sitemap\Location;
use GpsLab\Component\Sitemap\Render\SitemapRender;
use GpsLab\Component\Sitemap\Stream\Exception\LinksOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\SizeOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\WritingStream;
use GpsLab\Component\Sitemap\Url\Url;
use GpsLab\Component\Sitemap\Writer\Writer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class WritingStreamTest extends TestCase
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
     * @var string
     */
    private const FILENAME = '/var/www/sitemap.xml.gz';

    /**
     * @var MockObject&SitemapRender
     */
    private $render;

    /**
     * @var MockObject&Writer
     */
    private $writer;

    /**
     * @var WritingStream
     */
    private $stream;

    /**
     * @var int
     */
    private $render_call = 0;

    /**
     * @var int
     */
    private $write_call = 0;

    protected function setUp(): void
    {
        $this->render_call = 0;
        $this->write_call = 0;
        $this->render = $this->createMock(SitemapRender::class);
        $this->writer = $this->createMock(Writer::class);
        $this->stream = new WritingStream($this->render, $this->writer, self::FILENAME);
    }

    public function testOpenClose(): void
    {
        $this->expectOpen();
        $this->expectClose();

        $this->stream->open();
        $this->stream->close();
    }

    public function testAlreadyOpened(): void
    {
        $this->stream->open();

        $this->expectException(StreamStateException::class);
        $this->stream->open();
    }

    public function testCloseNotOpened(): void
    {
        $this->expectException(StreamStateException::class);
        $this->render
            ->expects(self::never())
            ->method('end')
        ;
        $this->writer
            ->expects(self::never())
            ->method('finish')
        ;

        $this->stream->close();
    }

    public function testCloseAlreadyClosed(): void
    {
        $this->stream->open();
        $this->stream->close();

        $this->expectException(StreamStateException::class);
        $this->stream->close();
    }

    public function testPushNotOpened(): void
    {
        $this->expectException(StreamStateException::class);
        $this->stream->push(Url::create('https://example.com/'));
    }

    public function testPushAfterClosed(): void
    {
        $this->stream->open();
        $this->stream->close();

        $this->expectException(StreamStateException::class);
        $this->stream->push(Url::create('https://example.com/'));
    }

    public function testPush(): void
    {
        $urls = [
            Url::create('https://example.com/foo'),
            Url::create('https://example.com/bar'),
            Url::create('https://example.com/baz'),
        ];

        // build expects
        $this->expectOpen();
        foreach ($urls as $i => $url) {
            $this->expectPush($url, (string) $url->getLocation());
        }
        $this->expectClose();

        // run test
        $this->stream->open();
        foreach ($urls as $url) {
            $this->stream->push($url);
        }
        $this->stream->close();
    }

    public function testOverflowLinks(): void
    {
        $url = Url::create('https://example.com/');

        $this->stream->open();

        for ($i = 0; $i < Limiter::LINKS_LIMIT; ++$i) {
            $this->stream->push($url);
        }

        $this->expectException(LinksOverflowException::class);
        $this->stream->push($url);
    }

    public function testOverflowSize(): void
    {
        $loops = (int) floor(Limiter::BYTE_LIMIT / Location::MAX_LENGTH);
        $prefix_size = Limiter::BYTE_LIMIT - ($loops * Location::MAX_LENGTH);
        $opened = str_repeat('<', $prefix_size);
        $location = 'https://example.com/';
        $location .= str_repeat('f', Location::MAX_LENGTH - strlen($location));
        $closed = '>'; // overflow byte

        $url = Url::create($location);

        $this->render
            ->expects(self::at($this->render_call++))
            ->method('start')
            ->willReturn($opened)
        ;
        $this->render
            ->expects(self::at($this->render_call++))
            ->method('end')
            ->willReturn($closed)
        ;
        $this->render
            ->expects(self::atLeastOnce())
            ->method('url')
            ->willReturn($location)
        ;

        $this->stream->open();

        $this->expectException(SizeOverflowException::class);

        for ($i = 0; $i < $loops; ++$i) {
            $this->stream->push($url);
        }
    }

    /**
     * @param string $opened
     * @param string $closed
     */
    private function expectOpen(string $opened = self::OPENED, string $closed = self::CLOSED): void
    {
        $this->render
            ->expects(self::at($this->render_call++))
            ->method('start')
            ->willReturn($opened)
        ;
        $this->render
            ->expects(self::at($this->render_call++))
            ->method('end')
            ->willReturn($closed)
        ;
        $this->writer
            ->expects(self::at($this->write_call++))
            ->method('start')
            ->with(self::FILENAME)
        ;
        $this->writer
            ->expects(self::at($this->write_call++))
            ->method('append')
            ->with($opened)
        ;
    }

    /**
     * @param string $closed
     */
    private function expectClose(string $closed = self::CLOSED): void
    {
        $this->writer
            ->expects(self::at($this->write_call++))
            ->method('append')
            ->with($closed)
        ;
        $this->writer
            ->expects(self::at($this->write_call++))
            ->method('finish')
        ;
    }

    /**
     * @param Url    $url
     * @param string $content
     */
    private function expectPush(Url $url, string $content): void
    {
        $this->render
            ->expects(self::at($this->render_call++))
            ->method('url')
            ->with($url)
            ->willReturn($content)
        ;
        $this->writer
            ->expects(self::at($this->write_call++))
            ->method('append')
            ->with($content)
        ;
    }
}
