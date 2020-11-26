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
use GpsLab\Component\Sitemap\Sitemap\Sitemap;
use GpsLab\Component\Sitemap\Stream\Exception\SplitIndexException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\WritingSplitStream;
use GpsLab\Component\Sitemap\Url\Url;
use GpsLab\Component\Sitemap\Writer\Writer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class WritingSplitStreamTest extends TestCase
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
    private const FILENAME = '/var/www/sitemap%d.xml.gz';

    /**
     * @var string
     */
    private const WEB_PATH = 'https://example.com/sitemap%d.xml.gz';

    /**
     * @var MockObject&SitemapRender
     */
    private $render;

    /**
     * @var MockObject&Writer
     */
    private $writer;

    /**
     * @var WritingSplitStream
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
        $this->stream = new WritingSplitStream($this->render, $this->writer, self::FILENAME, self::WEB_PATH);
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

    /**
     * @return string[][]
     */
    public function getBadPatterns(): array
    {
        return [
            ['sitemap.xml'],
            ['sitemap1.xml'],
            ['sitemap50000.xml'],
            ['sitemap12345.xml'],
        ];
    }

    /**
     * @dataProvider getBadPatterns
     *
     * @param string $filename
     */
    public function testBadFilenamePatterns(string $filename): void
    {
        $this->expectException(SplitIndexException::class);

        new WritingSplitStream($this->render, $this->writer, $filename, self::WEB_PATH);
    }

    /**
     * @dataProvider getBadPatterns
     *
     * @param string $web_path
     */
    public function testBadWebPathPatterns(string $web_path): void
    {
        $this->expectException(SplitIndexException::class);

        new WritingSplitStream($this->render, $this->writer, self::FILENAME, $web_path);
    }

    public function testGetEmptySitemapsList(): void
    {
        $this->expectOpen();
        $this->expectClose();

        $this->stream->open();
        self::assertEmpty(iterator_to_array($this->stream->getSitemaps()));
        $this->stream->close();
    }

    public function testGetSitemaps(): void
    {
        $url = Url::create('https://example.com/');
        $now = time();

        $this->expectOpen();
        $this->expectPush($url, (string) $url->getLocation());
        $this->expectClose();

        $this->stream->open();
        $this->stream->push($url);

        /* @var $sitemaps Sitemap[] */
        $sitemaps = iterator_to_array($this->stream->getSitemaps());

        self::assertCount(1, $sitemaps);
        self::assertInstanceOf(Sitemap::class, $sitemaps[0]);
        self::assertInstanceOf(\DateTimeInterface::class, $sitemaps[0]->getLastModify());
        self::assertGreaterThanOrEqual($now, $sitemaps[0]->getLastModify()->getTimestamp());
        self::assertEquals(sprintf(self::WEB_PATH, 1), (string) $sitemaps[0]->getLocation());

        $this->stream->close();

        // test clear list
        self::assertEmpty(iterator_to_array($this->stream->getSitemaps()));
    }

    public function testSplitOverflowLinks(): void
    {
        $url = Url::create('https://example.com/');
        $now = time();
        $overflow = 10;

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
        $this->render
            ->expects(self::exactly(Limiter::LINKS_LIMIT + $overflow))
            ->method('url')
        ;

        // reopen
        $this->writer
            ->expects(self::exactly(2))
            ->method('start')
        ;
        $this->writer
            ->expects(self::exactly(2))
            ->method('finish')
        ;

        $this->writer
            ->expects(self::exactly(Limiter::LINKS_LIMIT + 4 /* (start + end) * parts */ + $overflow))
            ->method('append')
        ;

        $this->stream->open();

        for ($i = 0; $i < Limiter::LINKS_LIMIT + $overflow; ++$i) {
            $this->stream->push($url);
        }

        /* @var $sitemaps Sitemap[] */
        $sitemaps = iterator_to_array($this->stream->getSitemaps());

        self::assertCount(2, $sitemaps);
        foreach ($sitemaps as $index => $sitemap) {
            self::assertInstanceOf(Sitemap::class, $sitemap);
            self::assertInstanceOf(\DateTimeInterface::class, $sitemap->getLastModify());
            self::assertGreaterThanOrEqual($now, $sitemap->getLastModify()->getTimestamp());
            self::assertEquals(sprintf(self::WEB_PATH, $index + 1), (string) $sitemap->getLocation());
        }

        $this->stream->close();

        // test clear list
        self::assertEmpty(iterator_to_array($this->stream->getSitemaps()));
    }

    public function testSplitOverflowSize(): void
    {
        $loops = (int) floor(Limiter::BYTE_LIMIT / Location::MAX_LENGTH);
        $prefix_size = Limiter::BYTE_LIMIT - ($loops * Location::MAX_LENGTH);
        $opened = str_repeat('<', $prefix_size);
        $location = 'https://example.com/';
        $location .= str_repeat('f', Location::MAX_LENGTH - strlen($location));
        $closed = '>'; // overflow byte

        $url = Url::create($location);
        $now = time();

        $this->render
            ->expects(self::once())
            ->method('start')
            ->willReturn($opened)
        ;
        $this->render
            ->expects(self::once())
            ->method('end')
            ->willReturn($closed)
        ;
        $this->render
            ->expects(self::exactly($loops + 1 /* overflow */))
            ->method('url')
            ->willReturn($location)
        ;

        // reopen
        $this->writer
            ->expects(self::exactly(2))
            ->method('start')
        ;
        $this->writer
            ->expects(self::exactly(2))
            ->method('finish')
        ;

        $this->writer
            ->expects(self::exactly($loops + 4 /* (start + end) * parts */))
            ->method('append')
        ;

        $this->stream->open();

        for ($i = 0; $i < $loops; ++$i) {
            $this->stream->push($url);
        }

        /* @var $sitemaps Sitemap[] */
        $sitemaps = iterator_to_array($this->stream->getSitemaps());

        self::assertCount(2, $sitemaps);
        foreach ($sitemaps as $index => $sitemap) {
            self::assertInstanceOf(Sitemap::class, $sitemap);
            self::assertInstanceOf(\DateTimeInterface::class, $sitemap->getLastModify());
            self::assertGreaterThanOrEqual($now, $sitemap->getLastModify()->getTimestamp());
            self::assertEquals(sprintf(self::WEB_PATH, $index + 1), (string) $sitemap->getLocation());
        }

        $this->stream->close();

        // test clear list
        self::assertEmpty(iterator_to_array($this->stream->getSitemaps()));
    }

    /**
     * @param int    $index
     * @param string $opened
     * @param string $closed
     */
    private function expectOpen(int $index = 1, string $opened = self::OPENED, string $closed = self::CLOSED): void
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
            ->with(sprintf(self::FILENAME, $index))
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
