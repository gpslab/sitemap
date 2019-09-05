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

use GpsLab\Component\Sitemap\Limiter;
use GpsLab\Component\Sitemap\Render\PlainTextSitemapIndexRender;
use GpsLab\Component\Sitemap\Render\PlainTextSitemapRender;
use GpsLab\Component\Sitemap\Render\SitemapIndexRender;
use GpsLab\Component\Sitemap\Render\SitemapRender;
use GpsLab\Component\Sitemap\Sitemap\Sitemap;
use GpsLab\Component\Sitemap\Stream\Exception\SitemapsOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\SplitIndexException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\WritingSplitIndexStream;
use GpsLab\Component\Sitemap\Url\Url;
use GpsLab\Component\Sitemap\Writer\FileWriter;
use GpsLab\Component\Sitemap\Writer\Writer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WritingSplitIndexStreamTest extends TestCase
{
    /**
     * @var string
     */
    private const INDEX_OPEN_TPL = 'Index stream opened';

    /**
     * @var string
     */
    private const INDEX_CLOSE_TPL = 'Index stream closed';

    /**
     * @var string
     */
    private const PART_OPEN_TPL = 'Part stream opened';

    /**
     * @var string
     */
    private const PART_CLOSE_TPL = 'Part stream closed';

    /**
     * @var string
     */
    private const URL_TPL = 'URL %s in sitemap';

    /**
     * @var string
     */
    private const SITEMAP_PART_TPL = 'Part %d of sitemap index';

    /**
     * @var string
     */
    private const SITEMAP_TPL = '%s of sitemap index';

    /**
     * @var string
     */
    private const INDEX_PATH = '/var/www/sitemap.xml';

    /**
     * @var string
     */
    private const PART_PATH = '/var/www/sitemap%d.xml';

    /**
     * @var string
     */
    private const PART_WEB_PATH = '/sitemap%d.xml';

    /**
     * @var MockObject|SitemapIndexRender
     */
    private $index_render;

    /**
     * @var MockObject|SitemapRender
     */
    private $part_render;

    /**
     * @var MockObject|Writer
     */
    private $index_writer;

    /**
     * @var MockObject|Writer
     */
    private $part_writer;

    /**
     * @var WritingSplitIndexStream
     */
    private $stream;

    /**
     * @var int
     */
    private $index_render_call = 0;

    /**
     * @var int
     */
    private $index_write_call = 0;

    /**
     * @var int
     */
    private $part_render_call = 0;

    /**
     * @var int
     */
    private $part_write_call = 0;

    /**
     * @var string
     */
    private $tmp_index_filename;

    /**
     * @var string
     */
    private $tmp_part_filename;

    protected function setUp(): void
    {
        $this->index_render_call = 0;
        $this->index_write_call = 0;
        $this->part_render_call = 0;
        $this->part_write_call = 0;
        $this->tmp_index_filename = '';
        $this->tmp_part_filename = '';

        $this->index_render = $this->createMock(SitemapIndexRender::class);
        $this->part_render = $this->createMock(SitemapRender::class);
        $this->index_writer = $this->createMock(Writer::class);
        $this->part_writer = $this->createMock(Writer::class);

        $this->stream = new WritingSplitIndexStream(
            $this->index_render,
            $this->part_render,
            $this->index_writer,
            $this->part_writer,
            self::INDEX_PATH,
            self::PART_PATH
        );
    }

    protected function tearDown(): void
    {
        if ($this->tmp_index_filename && file_exists($this->tmp_index_filename)) {
            unlink($this->tmp_index_filename);
        }

        if ($this->tmp_part_filename && file_exists($this->tmp_part_filename)) {
            unlink($this->tmp_part_filename);
        }
    }

    public function testAlreadyOpened(): void
    {
        $this->expectOpen();
        $this->expectOpenPart();
        $this->stream->open();

        $this->expectException(StreamStateException::class);
        $this->stream->open();
    }

    public function testCloseNotOpened(): void
    {
        $this->expectException(StreamStateException::class);
        $this->stream->close();
    }

    public function testCloseAlreadyClosed(): void
    {
        $this->expectOpen();
        $this->expectOpenPart();
        $this->expectClosePart();
        $this->expectClose();

        $this->stream->open();
        $this->stream->close();

        $this->expectException(StreamStateException::class);
        $this->stream->close();
    }

    public function testPushNotOpened(): void
    {
        $this->expectException(StreamStateException::class);
        $this->stream->push(new Url('/'));
    }

    public function testPushSitemapNotOpened(): void
    {
        $this->expectException(StreamStateException::class);
        $this->stream->pushSitemap(new Sitemap('/sitemap_news.xml'));
    }

    public function testPushAfterClosed(): void
    {
        $this->expectOpen();
        $this->expectOpenPart();
        $this->expectClosePart();
        $this->expectClose();

        $this->stream->open();
        $this->stream->close();

        $this->expectException(StreamStateException::class);
        $this->stream->push(new Url('/'));
    }

    public function testEmptyIndex(): void
    {
        $this->expectOpen();
        $this->expectOpenPart();
        $this->expectClosePart();
        $this->expectClose();

        $this->index_render
            ->expects(self::never())
            ->method('sitemap')
        ;

        $this->stream->open();
        $this->stream->close();
    }

    /**
     * @return array
     */
    public function getPartFilenames(): array
    {
        return [
            ['sitemap.xml', 'sitemap1.xml'],
            ['sitemap.xml.gz', 'sitemap1.xml.gz'], // custom filename extension
            ['sitemap_part.xml', 'sitemap_part1.xml'], // custom filename
            ['/sitemap.xml', '/sitemap1.xml'], // in root folder
            ['/var/www/sitemap.xml', '/var/www/sitemap1.xml'], // in folder
        ];
    }

    /**
     * @dataProvider getPartFilenames
     *
     * @param string $index_filename
     * @param string $part_filename
     */
    public function testPartFilenames(string $index_filename, string $part_filename): void
    {
        $this->expectOpen($index_filename);
        $this->expectOpenPart($part_filename);
        $this->expectClosePart();
        $this->expectClose();

        $this->stream = new WritingSplitIndexStream(
            $this->index_render,
            $this->part_render,
            $this->index_writer,
            $this->part_writer,
            $index_filename
        );

        $this->stream->open();
        $this->stream->close();
    }

    /**
     * @return array
     */
    public function getBadPartFilenamePatterns(): array
    {
        return [
            ['sitemap.xml', 'sitemap.xml'],
            ['sitemap1.xml', 'sitemap1.xml'],
            ['sitemap50000.xml', 'sitemap50000.xml'],
            ['sitemap12345.xml', 'sitemap12345.xml'],
            ['sitemap.xml', 'sitemap1.xml'],
            ['sitemap.xml', 'sitemap50000.xml'],
            ['sitemap.xml', 'sitemap12345.xml'],
        ];
    }

    /**
     * @dataProvider getBadPartFilenamePatterns
     *
     * @param string $index_filename
     * @param string $part_filename
     */
    public function testBadPartFilenamesPatterns(string $index_filename, string $part_filename): void
    {
        $this->expectException(SplitIndexException::class);

        new WritingSplitIndexStream(
            $this->index_render,
            $this->part_render,
            $this->index_writer,
            $this->part_writer,
            $index_filename,
            $part_filename
        );
    }

    public function testConflictWriters(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('fwrite() expects parameter 1 to be resource, null given');

        $writer = new FileWriter();
        $this->tmp_index_filename = tempnam(sys_get_temp_dir(), 'sitemap');
        $this->tmp_part_filename = tempnam(sys_get_temp_dir(), 'sitemap%d');

        $stream = new WritingSplitIndexStream(
            new PlainTextSitemapIndexRender('https://example.com'),
            new PlainTextSitemapRender('https://example.com'),
            $writer,
            $writer,
            $this->tmp_index_filename,
            $this->tmp_part_filename
        );

        $stream->open();
        $stream->close();
    }

    public function testPush(): void
    {
        $urls = [
            new Url('/foo'),
            new Url('/bar'),
            new Url('/baz'),
        ];

        $this->expectOpen();
        $this->expectOpenPart();

        foreach ($urls as $url) {
            /* @var $url Url */
            $this->expectPushToPart($url);
        }

        $this->expectClosePart();

        $this->index_render
            ->expects(self::at($this->index_render_call++))
            ->method('sitemap')
            ->willReturnCallback(static function ($sitemap) {
                /* @var Sitemap $sitemap */
                self::assertInstanceOf(Sitemap::class, $sitemap);
                self::assertEquals(sprintf(self::PART_WEB_PATH, 1), $sitemap->getLocation());
                self::assertInstanceOf(\DateTimeImmutable::class, $sitemap->getLastModify());

                return sprintf(self::SITEMAP_PART_TPL, 1);
            })
        ;
        $this->index_writer
            ->expects(self::at($this->index_write_call++))
            ->method('append')
            ->with(sprintf(self::SITEMAP_PART_TPL, 1))
        ;

        $this->expectClose();

        $this->stream->open();
        foreach ($urls as $url) {
            $this->stream->push($url);
        }
        $this->stream->close();
    }

    public function testSplitOverflowLinks(): void
    {
        $url = new Url('/');

        $this->expectOpen();
        $this->expectOpenPart();

        // add first part to sitemap index
        $this->index_render
            ->expects(self::at($this->index_render_call++))
            ->method('sitemap')
            ->willReturnCallback(static function ($sitemap) {
                /* @var Sitemap $sitemap */
                self::assertInstanceOf(Sitemap::class, $sitemap);
                self::assertEquals(sprintf(self::PART_WEB_PATH, 1), $sitemap->getLocation());
                self::assertInstanceOf(\DateTimeImmutable::class, $sitemap->getLastModify());

                return sprintf(self::PART_WEB_PATH, 1);
            })
        ;
        $this->index_writer
            ->expects(self::at($this->index_write_call++))
            ->method('append')
            ->with(sprintf(self::PART_WEB_PATH, 1))
        ;

        // reopen
        $this->part_writer
            ->expects(self::exactly(2))
            ->method('start')
        ;
        $this->part_writer
            ->expects(self::exactly(2))
            ->method('finish')
        ;

        $this->part_render
            ->expects(self::once())
            ->method('start')
        ;
        $this->part_render
            ->expects(self::once())
            ->method('end')
        ;

        // add second part to sitemap index
        $this->index_render
            ->expects(self::at($this->index_render_call++))
            ->method('sitemap')
            ->willReturnCallback(static function ($sitemap) {
                /* @var Sitemap $sitemap */
                self::assertInstanceOf(Sitemap::class, $sitemap);
                self::assertEquals(sprintf(self::PART_WEB_PATH, 2), $sitemap->getLocation());
                self::assertInstanceOf(\DateTimeImmutable::class, $sitemap->getLastModify());

                return sprintf(self::PART_WEB_PATH, 2);
            })
        ;
        $this->index_writer
            ->expects(self::at($this->index_write_call++))
            ->method('append')
            ->with(sprintf(self::PART_WEB_PATH, 2))
        ;
        $this->expectClose();

        $this->stream->open();
        for ($i = 0; $i <= Limiter::LINKS_LIMIT; ++$i) {
            $this->stream->push($url);
        }
        $this->stream->close();
    }

    public function testSplitOverflowSize(): void
    {
        $url = new Url('/');
        $loops = 10000;
        $loop_size = (int) floor(Limiter::BYTE_LIMIT / $loops);
        $prefix_size = Limiter::BYTE_LIMIT - ($loops * $loop_size);
        $url_tpl = str_repeat('/', $loop_size);
        $open = str_repeat('/', $prefix_size);
        $close = '/'; // overflow byte

        $this->expectOpen();
        $this->expectOpenPart('', $open, $close);

        // add first part to sitemap index
        $this->index_render
            ->expects(self::at($this->index_render_call++))
            ->method('sitemap')
            ->willReturnCallback(static function ($sitemap) {
                /* @var Sitemap $sitemap */
                self::assertInstanceOf(Sitemap::class, $sitemap);
                self::assertEquals(sprintf(self::PART_WEB_PATH, 1), $sitemap->getLocation());
                self::assertInstanceOf(\DateTimeImmutable::class, $sitemap->getLastModify());

                return sprintf(self::PART_WEB_PATH, 1);
            })
        ;
        $this->index_writer
            ->expects(self::at($this->index_write_call++))
            ->method('append')
            ->with(sprintf(self::PART_WEB_PATH, 1))
        ;
        $this->part_render
            ->expects(self::at($this->part_render_call++))
            ->method('url')
            ->with($url)
            ->willReturn($url_tpl)
        ;

        // reopen
        $this->part_writer
            ->expects(self::exactly(2))
            ->method('start')
        ;
        $this->part_writer
            ->expects(self::exactly(2))
            ->method('finish')
        ;

        $this->part_render
            ->expects(self::once())
            ->method('start')
        ;
        $this->part_render
            ->expects(self::once())
            ->method('end')
        ;

        // add second part to sitemap index
        $this->index_render
            ->expects(self::at($this->index_render_call++))
            ->method('sitemap')
            ->willReturnCallback(static function ($sitemap) {
                /* @var Sitemap $sitemap */
                self::assertInstanceOf(Sitemap::class, $sitemap);
                self::assertEquals(sprintf(self::PART_WEB_PATH, 2), $sitemap->getLocation());
                self::assertInstanceOf(\DateTimeImmutable::class, $sitemap->getLastModify());

                return sprintf(self::PART_WEB_PATH, 2);
            })
        ;
        $this->index_writer
            ->expects(self::at($this->index_write_call++))
            ->method('append')
            ->with(sprintf(self::PART_WEB_PATH, 2))
        ;
        $this->expectClose();

        $this->stream->open();
        for ($i = 0; $i <= Limiter::LINKS_LIMIT; ++$i) {
            $this->stream->push($url);
        }
        $this->stream->close();
    }

    public function testOverflow(): void
    {
        $this->markTestSkipped('This test performs 2 500 000 000 iterations, so it is too large.');

        $this->expectException(SitemapsOverflowException::class);

        $this->expectOpen();
        $this->expectOpenPart();

        $url = new Url('/foo');
        $this->stream->open();
        for ($i = 0; $i <= Limiter::SITEMAPS_LIMIT * Limiter::LINKS_LIMIT; ++$i) {
            $this->stream->push($url);
        }
    }

    public function testPushSitemap(): void
    {
        $sitemap = new Sitemap('/sitemap_news.xml');

        $this->expectOpen();
        $this->expectOpenPart();

        $this->index_render
            ->expects(self::at($this->index_render_call++))
            ->method('sitemap')
            ->with($sitemap)
            ->willReturn(self::SITEMAP_TPL)
        ;

        $this->index_writer
            ->expects(self::at($this->index_write_call++))
            ->method('append')
            ->with(self::SITEMAP_TPL)
        ;
        $this->expectClosePart();
        $this->expectClose();

        $this->stream->open();
        $this->stream->pushSitemap($sitemap);
        $this->stream->close();
    }

    public function testPushSitemapOverflow(): void
    {
        $this->expectException(SitemapsOverflowException::class);

        $this->expectOpen();
        $this->expectOpenPart();

        $sitemap = new Sitemap('/sitemap_news.xml');
        $this->stream->open();
        for ($i = 0; $i <= Limiter::SITEMAPS_LIMIT; ++$i) {
            $this->stream->pushSitemap($sitemap);
        }
    }

    /**
     * @param string $path
     * @param string $open
     */
    private function expectOpen(string $path = self::INDEX_PATH, string $open = self::INDEX_OPEN_TPL): void
    {
        $this->index_render
            ->expects(self::at($this->index_render_call++))
            ->method('start')
            ->willReturn($open)
        ;

        $this->index_writer
            ->expects(self::at($this->index_write_call++))
            ->method('start')
            ->with($path)
        ;
        $this->index_writer
            ->expects(self::at($this->index_write_call++))
            ->method('append')
            ->with($open)
        ;
    }

    /**
     * @param string $close
     */
    private function expectClose(string $close = self::INDEX_CLOSE_TPL): void
    {
        $this->index_render
            ->expects(self::at($this->index_render_call++))
            ->method('end')
            ->willReturn($close)
        ;

        $this->index_writer
            ->expects(self::at($this->index_write_call++))
            ->method('append')
            ->with($close)
        ;
        $this->index_writer
            ->expects(self::at($this->index_write_call++))
            ->method('finish')
        ;
    }

    /**
     * @param string $path
     * @param string $open
     * @param string $close
     */
    private function expectOpenPart(
        string $path = '',
        string $open = self::PART_OPEN_TPL,
        string $close = self::PART_CLOSE_TPL
    ): void {
        $this->part_render
            ->expects(self::at($this->part_render_call++))
            ->method('start')
            ->willReturn($open)
        ;
        $this->part_render
            ->expects(self::at($this->part_render_call++))
            ->method('end')
            ->willReturn($close)
        ;

        $this->part_writer
            ->expects(self::at($this->part_write_call++))
            ->method('start')
            ->with($path ?: sprintf(self::PART_PATH, 1))
        ;
        $this->part_writer
            ->expects(self::at($this->part_write_call++))
            ->method('append')
            ->with($open)
        ;
    }

    /**
     * @param string $close
     */
    private function expectClosePart(string $close = self::PART_CLOSE_TPL): void
    {
        $this->part_writer
            ->expects(self::at($this->part_write_call++))
            ->method('append')
            ->with($close)
        ;
        $this->part_writer
            ->expects(self::at($this->part_write_call++))
            ->method('finish')
        ;
    }

    /**
     * @param Url    $url
     * @param string $url_tpl
     */
    private function expectPushToPart(URL $url, string $url_tpl = ''): void
    {
        $this->part_render
            ->expects(self::at($this->part_render_call++))
            ->method('url')
            ->with($url)
            ->willReturn($url_tpl ?: sprintf(self::URL_TPL, $url->getLocation()))
        ;
        $this->part_writer
            ->expects(self::at($this->part_write_call++))
            ->method('append')
            ->with($url_tpl ?: sprintf(self::URL_TPL, $url->getLocation()))
        ;
    }
}
