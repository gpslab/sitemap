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
use GpsLab\Component\Sitemap\Render\SitemapIndexRender;
use GpsLab\Component\Sitemap\Sitemap\Sitemap;
use GpsLab\Component\Sitemap\Stream\Exception\SitemapsOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\WritingIndexStream;
use GpsLab\Component\Sitemap\Writer\Writer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class WritingIndexStreamTest extends TestCase
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
     * @var MockObject&SitemapIndexRender
     */
    private $render;

    /**
     * @var MockObject&Writer
     */
    private $writer;

    /**
     * @var WritingIndexStream
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
        $this->render = $this->createMock(SitemapIndexRender::class);
        $this->writer = $this->createMock(Writer::class);
        $this->stream = new WritingIndexStream($this->render, $this->writer, self::FILENAME);
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
        $this->stream->pushSitemap(new Sitemap('https://example.com/sitemap_news.xml'));
    }

    public function testPushAfterClosed(): void
    {
        $this->stream->open();
        $this->stream->close();

        $this->expectException(StreamStateException::class);
        $this->stream->pushSitemap(new Sitemap('https://example.com/sitemap_news.xml'));
    }

    public function testPush(): void
    {
        $sitemaps = [
            new Sitemap('https://example.com/sitemap_foo.xml'),
            new Sitemap('https://example.com/sitemap_bar.xml'),
            new Sitemap('https://example.com/sitemap_baz.xml'),
        ];

        // build expects
        $this->expectOpen();
        foreach ($sitemaps as $i => $sitemap) {
            $this->expectPush($sitemap, (string) $sitemap->getLocation());
        }
        $this->expectClose();

        // run test
        $this->stream->open();
        foreach ($sitemaps as $sitemap) {
            $this->stream->pushSitemap($sitemap);
        }
        $this->stream->close();
    }

    public function testOverflowLinks(): void
    {
        $sitemap = new Sitemap('https://example.com/sitemap_news.xml');

        $this->stream->open();

        for ($i = 0; $i < Limiter::LINKS_LIMIT; ++$i) {
            $this->stream->pushSitemap($sitemap);
        }

        $this->expectException(SitemapsOverflowException::class);
        $this->stream->pushSitemap($sitemap);
    }

    /**
     * @param string $opened
     */
    private function expectOpen(string $opened = self::OPENED): void
    {
        $this->render
            ->expects(self::at($this->render_call++))
            ->method('start')
            ->willReturn($opened)
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
        $this->render
            ->expects(self::at($this->render_call++))
            ->method('end')
            ->willReturn($closed)
        ;
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
     * @param Sitemap $sitemap
     * @param string  $content
     */
    private function expectPush(Sitemap $sitemap, string $content): void
    {
        $this->render
            ->expects(self::at($this->render_call++))
            ->method('sitemap')
            ->with($sitemap)
            ->willReturn($content)
        ;
        $this->writer
            ->expects(self::at($this->write_call++))
            ->method('append')
            ->with($content)
        ;
    }
}
