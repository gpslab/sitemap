<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Unit\Stream;

use GpsLab\Component\Sitemap\Render\SitemapIndexRender;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\FileStream;
use GpsLab\Component\Sitemap\Stream\RenderIndexFileStream;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RenderIndexFileStreamTest extends TestCase
{
    /**
     * @var MockObject|SitemapIndexRender
     */
    private $render;

    /**
     * @var RenderIndexFileStream
     */
    private $stream;

    /**
     * @var MockObject|FileStream
     */
    private $substream;

    /**
     * @var string
     */
    private $expected_content = '';

    /**
     * @var string
     */
    private $host = 'https://example.com/';

    /**
     * @var string
     */
    private $filename = '';

    /**
     * @var string
     */
    private $subfilename = '';

    /**
     * @var int
     */
    private $index = 0;

    protected function setUp(): void
    {
        if (!$this->filename) {
            $this->filename = tempnam(sys_get_temp_dir(), 'idx').'.xml';
        }
        if (!$this->subfilename) {
            $this->subfilename = tempnam(sys_get_temp_dir(), 'tsp').'.xml';
        }
        file_put_contents($this->filename, '');
        file_put_contents($this->subfilename, '');

        $this->render = $this->createMock(SitemapIndexRender::class);
        $this->substream = $this->createMock(FileStream::class);
        $this->stream = new RenderIndexFileStream($this->render, $this->substream, $this->host, $this->filename);
    }

    protected function tearDown(): void
    {
        self::assertEquals($this->expected_content, file_get_contents($this->filename));

        unset($this->stream);
        unlink($this->filename);
        if (file_exists($this->subfilename)) {
            unlink($this->subfilename);
        }

        for ($i = 0; $i < $this->index; ++$i) {
            $filename = $this->getFilenameOfIndex($i + 1);
            self::assertFileExists(sys_get_temp_dir().'/'.$filename);
            unlink(sys_get_temp_dir().'/'.$filename);
        }

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
            $this->substream
                ->expects(self::at($i))
                ->method('push')
                ->with($urls[$i])
                ->will(self::returnValue($url->getLoc()))
            ;
        }

        foreach ($urls as $url) {
            $this->stream->push($url);
        }

        $this->close();
    }

    private function open(): void
    {
        ++$this->index;
        $opened = 'Stream opened';
        $this->render
            ->expects(self::at(0))
            ->method('start')
            ->will(self::returnValue($opened))
        ;
        $this->render
            ->expects(self::at(2))
            ->method('sitemap')
            ->will(self::returnCallback(function ($url, $last_mod) {
                self::assertInstanceOf(\DateTimeImmutable::class, $last_mod);
                self::assertEquals($this->host, substr($url, 0, strlen($this->host)));
                self::assertEquals($this->getFilenameOfIndex($this->index), substr($url, strlen($this->host)));
            }))
        ;

        $this->substream
            ->expects(self::atLeastOnce())
            ->method('open')
        ;
        $this->substream
            ->expects(self::atLeastOnce())
            ->method('getFilename')
            ->will(self::returnValue($this->subfilename))
        ;

        $this->stream->open();
        $this->expected_content .= $opened;
    }

    private function close(): void
    {
        $closed = 'Stream closed';
        $this->render
            ->expects(self::at(1))
            ->method('end')
            ->will(self::returnValue($closed))
        ;

        $this->substream
            ->expects(self::atLeastOnce())
            ->method('close')
        ;

        $this->stream->close();
        $this->expected_content .= $closed;
    }

    /**
     * @param int $index
     *
     * @return string
     */
    private function getFilenameOfIndex(int $index): string
    {
        // use explode() for correct add index
        // sitemap.xml -> sitemap1.xml
        // sitemap.xml.gz -> sitemap1.xml.gz

        list($filename, $extension) = explode('.', basename($this->subfilename), 2);

        return sprintf('%s%s.%s', $filename, $index, $extension);
    }
}
