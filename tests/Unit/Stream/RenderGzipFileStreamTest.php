<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Unit\Stream;

use GpsLab\Component\Sitemap\Render\SitemapRender;
use GpsLab\Component\Sitemap\Stream\Exception\CompressionLevelException;
use GpsLab\Component\Sitemap\Stream\Exception\FileAccessException;
use GpsLab\Component\Sitemap\Stream\Exception\LinksOverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\RenderGzipFileStream;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RenderGzipFileStreamTest extends TestCase
{
    /**
     * @var MockObject|SitemapRender
     */
    private $render;

    /**
     * @var RenderGzipFileStream
     */
    private $stream;

    /**
     * @var string
     */
    private $expected_content = '';

    /**
     * @var string
     */
    private $filename = '';

    /**
     * @var string
     */
    private $opened = 'Stream opened';

    /**
     * @var string
     */
    private $closed = 'Stream closed';

    protected function setUp(): void
    {
        if (!$this->filename) {
            $this->filename = tempnam(sys_get_temp_dir(), 'sitemap');
        }
        file_put_contents($this->filename, '');

        $this->render = $this->createMock(SitemapRender::class);
        $this->stream = new RenderGzipFileStream($this->render, $this->filename);
    }

    protected function tearDown(): void
    {
        self::assertEquals($this->expected_content, $this->getContent());

        unlink($this->filename);
        $this->expected_content = '';
    }

    public function testGetFilename(): void
    {
        self::assertEquals($this->filename, $this->stream->getFilename());
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
        $this->open();

        $urls = [
            new Url('/foo'),
            new Url('/bar'),
            new Url('/baz'),
        ];

        foreach ($urls as $i => $url) {
            /* @var $url Url */
            $this->render
                ->expects(self::at($i))
                ->method('url')
                ->with($urls[$i])
                ->will(self::returnValue($url->getLoc()))
            ;
            $this->expected_content .= $url->getLoc();
        }

        foreach ($urls as $url) {
            $this->stream->push($url);
        }

        $this->close();
    }

    /**
     * @return array
     */
    public function compressionLevels(): array
    {
        return [
            [0],
            [-1],
            [10],
        ];
    }

    /**
     * @dataProvider compressionLevels
     *
     * @param int $compression_level
     */
    public function testInvalidCompressionLevel(int $compression_level): void
    {
        $this->expectException(CompressionLevelException::class);
        $this->stream = new RenderGzipFileStream($this->render, $this->filename, $compression_level);
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
            for ($i = 0; $i <= RenderGzipFileStream::LINKS_LIMIT; ++$i) {
                $this->stream->push(new Url($loc));
            }
            self::assertTrue(false, 'Must throw LinksOverflowException.');
        } catch (LinksOverflowException $e) {
            $this->stream->close();
            file_put_contents($this->filename, ''); // not check content
        }
    }

    public function testNotWritable(): void
    {
        try {
            $this->stream = new RenderGzipFileStream($this->render, '');
            $this->stream->open();
            self::assertTrue(false, 'Must throw FileAccessException.');
        } catch (FileAccessException $e) {
            try {
                unset($this->stream);
            } catch (StreamStateException $e) {
                // impossible correct close stream because it is incorrect opened
            }
        }
    }

    private function open(): void
    {
        $this->render
            ->expects(self::at(0))
            ->method('start')
            ->will(self::returnValue($this->opened))
        ;
        $this->render
            ->expects(self::at(1))
            ->method('end')
            ->will(self::returnValue($this->closed))
        ;

        $this->stream->open();
        $this->expected_content .= $this->opened;
    }

    private function close(): void
    {
        $this->stream->close();
        $this->expected_content .= $this->closed;
    }

    /**
     * @return string
     */
    private function getContent(): string
    {
        $content = '';
        $handle = gzopen($this->filename, 'r');
        while (!feof($handle)) {
            $content .= fread($handle, 1024);
        }
        gzclose($handle);

        return $content;
    }
}
