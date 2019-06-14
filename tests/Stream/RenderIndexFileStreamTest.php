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

use GpsLab\Component\Sitemap\Render\PlainTextSitemapIndexRender;
use GpsLab\Component\Sitemap\Render\PlainTextSitemapRender;
use GpsLab\Component\Sitemap\Render\SitemapIndexRender;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\FileStream;
use GpsLab\Component\Sitemap\Stream\RenderFileStream;
use GpsLab\Component\Sitemap\Stream\RenderIndexFileStream;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\TestCase;

class RenderIndexFileStreamTest extends TestCase
{
    /**
     * @var SitemapIndexRender
     */
    private $render;

    /**
     * @var RenderIndexFileStream
     */
    private $stream;

    /**
     * @var FileStream
     */
    private $substream;

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
    private $subfilename = '';

    protected function setUp(): void
    {
        $this->expected_content = '';
    }

    protected function tearDown(): void
    {
        try {
            $this->stream->close();
        } catch (StreamStateException $e) {
            // already closed exception is correct error
            // test correct saved content
            if ($this->expected_content) {
                self::assertEquals($this->expected_content, file_get_contents($this->filename));
            }
        }

        foreach (glob(sys_get_temp_dir().'/sitemap*') as $filename) {
            unlink($filename);
        }

        $this->expected_content = '';
    }

    /**
     * @param string $subfilename
     */
    private function initStream(string $subfilename = 'sitemap.xml'): void
    {
        $this->filename = sys_get_temp_dir().'/sitemap.xml';
        $this->subfilename = sys_get_temp_dir().'/'.$subfilename;

        $this->render = new PlainTextSitemapIndexRender('example.com');
        $this->substream = new RenderFileStream(new PlainTextSitemapRender(), $this->subfilename);
        $this->stream = new RenderIndexFileStream($this->render, $this->substream, $this->filename);
    }

    public function testGetFilename(): void
    {
        $this->initStream();
        self::assertEquals($this->filename, $this->stream->getFilename());
    }

    public function testAlreadyOpened(): void
    {
        $this->initStream();
        $this->expectException(StreamStateException::class);
        $this->expected_content = $this->render->start();
        $this->stream->open();
        $this->stream->open();
    }

    public function testNotOpened(): void
    {
        $this->initStream();
        $this->expectException(StreamStateException::class);
        $this->stream->close();
    }

    public function testAlreadyClosed(): void
    {
        $this->initStream();
        $this->expectException(StreamStateException::class);
        $this->expected_content = $this->render->start().$this->render->end();
        $this->stream->open();
        $this->stream->close();
        $this->stream->close();
    }

    public function testPushNotOpened(): void
    {
        $this->initStream();
        $this->expectException(StreamStateException::class);
        $this->stream->push(new Url('/'));
    }

    public function testPushClosed(): void
    {
        $this->initStream();
        $this->expectException(StreamStateException::class);
        $this->expected_content = $this->render->start().$this->render->end();
        $this->stream->open();
        $this->stream->close();

        $this->stream->push(new Url('/'));
    }

    public function testEmptyIndex(): void
    {
        $this->initStream();
        $this->expected_content = $this->render->start().$this->render->end();
        $this->stream->open();
        $this->stream->close();

        self::assertFileExists($this->filename);
        self::assertFileNotExists(sys_get_temp_dir().'/sitemap1.xml');
    }

    /**
     * @return array
     */
    public function getSubfilenames(): array
    {
        return [
            ['sitemap.xml', 'sitemap1.xml'],
            ['sitemap.xml.gz', 'sitemap1.xml.gz'], // custom filename extension
            ['sitemap_part.xml', 'sitemap_part1.xml'], // custom filename
        ];
    }

    /**
     * @dataProvider getSubfilenames
     *
     * @param string $subfilename
     * @param string $indexed_filename
     */
    public function testPush(string $subfilename, string $indexed_filename): void
    {
        $this->initStream($subfilename);

        $urls = [
            new Url('/foo'),
            new Url('/bar'),
            new Url('/baz'),
        ];

        $this->stream->open();
        foreach ($urls as $url) {
            $this->stream->push($url);
        }
        $this->stream->close();

        $time = filemtime(dirname($this->subfilename).'/'.$indexed_filename);
        $last_mod = (new \DateTimeImmutable())->setTimestamp($time);

        $this->expected_content = $this->render->start().
            $this->render->sitemap($indexed_filename, $last_mod).
            $this->render->end();

        self::assertFileExists($this->filename);
        self::assertFileExists(sys_get_temp_dir().'/'.$indexed_filename);
    }

    public function testOverflow(): void
    {
        $this->initStream('sitemap.xml');
        $this->stream->open();
        for ($i = 0; $i <= RenderFileStream::LINKS_LIMIT; ++$i) {
            $this->stream->push(new Url('/'));
        }
        $this->stream->close();

        self::assertFileExists($this->filename);
        self::assertFileExists(sys_get_temp_dir().'/sitemap1.xml');
        self::assertFileExists(sys_get_temp_dir().'/sitemap2.xml');
        self::assertFileNotExists(sys_get_temp_dir().'/sitemap3.xml');
    }
}
