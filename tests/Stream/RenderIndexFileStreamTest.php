<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
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

class RenderIndexFileStreamTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->expected_content = '';
    }

    protected function tearDown()
    {
        try {
            $this->stream->close();
        } catch (StreamStateException $e) {
            // already closed exception is correct error
            // test correct saved content
            if ($this->expected_content) {
                $this->assertEquals($this->expected_content, file_get_contents($this->filename));
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
    private function initStream($subfilename = 'sitemap.xml')
    {
        $this->filename = sys_get_temp_dir().'/sitemap.xml';
        $this->subfilename = sys_get_temp_dir().'/'.$subfilename;

        $this->render = new PlainTextSitemapIndexRender();
        $this->substream = new RenderFileStream(new PlainTextSitemapRender(), $this->subfilename);
        $this->stream = new RenderIndexFileStream(
            $this->render,
            $this->substream,
            'http://example.com/',
            $this->filename
        );
    }

    public function testGetFilename()
    {
        $this->initStream();
        $this->assertEquals($this->filename, $this->stream->getFilename());
    }

    /**
     * @expectedException \GpsLab\Component\Sitemap\Stream\Exception\StreamStateException
     */
    public function testAlreadyOpened()
    {
        $this->initStream();
        $this->expected_content = $this->render->start();
        $this->stream->open();
        $this->stream->open();
    }

    /**
     * @expectedException \GpsLab\Component\Sitemap\Stream\Exception\StreamStateException
     */
    public function testNotOpened()
    {
        $this->initStream();
        $this->stream->close();
    }

    /**
     * @expectedException \GpsLab\Component\Sitemap\Stream\Exception\StreamStateException
     */
    public function testAlreadyClosed()
    {
        $this->initStream();
        $this->expected_content = $this->render->start().$this->render->end();
        $this->stream->open();
        $this->stream->close();
        $this->stream->close();
    }

    /**
     * @expectedException \GpsLab\Component\Sitemap\Stream\Exception\StreamStateException
     */
    public function testPushNotOpened()
    {
        $this->initStream();
        $this->stream->push(new Url('/'));
    }

    /**
     * @expectedException \GpsLab\Component\Sitemap\Stream\Exception\StreamStateException
     */
    public function testPushClosed()
    {
        $this->initStream();
        $this->expected_content = $this->render->start().$this->render->end();
        $this->stream->open();
        $this->stream->close();

        $this->stream->push(new Url('/'));
    }

    public function testEmptyIndex()
    {
        $this->initStream();
        $this->expected_content = $this->render->start().$this->render->end();
        $this->stream->open();
        $this->stream->close();

        $this->assertFileExists($this->filename);
        $this->assertFileNotExists(sys_get_temp_dir().'/sitemap1.xml');
    }

    /**
     * @return array
     */
    public function getSubfilenames()
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
    public function testPush($subfilename, $indexed_filename)
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
        $total = count($this->stream);
        $this->stream->close();

        $time = filemtime(dirname($this->subfilename).'/'.$indexed_filename);
        $last_mod = (new \DateTimeImmutable())->setTimestamp($time);

        $this->expected_content = $this->render->start().
            $this->render->sitemap('http://example.com/'.$indexed_filename, $last_mod).
            $this->render->end();

        $this->assertFileExists($this->filename);
        $this->assertFileExists(sys_get_temp_dir().'/'.$indexed_filename);
        $this->assertEquals(count($urls), $total);
        $this->assertEquals(0, count($this->stream));
    }

    public function testOverflow()
    {
        $this->initStream('sitemap.xml');
        $this->stream->open();
        for ($i = 0; $i <= RenderFileStream::LINKS_LIMIT; ++$i) {
            $this->stream->push(new Url('/'));
        }
        $total = count($this->stream);
        $this->stream->close();

        $this->assertFileExists($this->filename);
        $this->assertFileExists(sys_get_temp_dir().'/sitemap1.xml');
        $this->assertFileExists(sys_get_temp_dir().'/sitemap2.xml');
        $this->assertFileNotExists(sys_get_temp_dir().'/sitemap3.xml');
        $this->assertEquals(RenderFileStream::LINKS_LIMIT + 1, $total);
        $this->assertEquals(0, count($this->stream));
    }

    /**
     * @expectedException \GpsLab\Component\Sitemap\Stream\Exception\FileAccessException
     */
    public function testNotReadable()
    {
        $this->filename = sys_get_temp_dir().'/sitemap.xml';

        $this->substream = $this->getMockBuilder(RenderFileStream::class)->disableOriginalConstructor()->getMock();
        $this->render = new PlainTextSitemapIndexRender();
        $this->stream = new RenderIndexFileStream(
            $this->render,
            $this->substream,
            'http://example.com',
            $this->filename
        );

        $this->stream->open();
        $this->stream->push(new Url('/foo'));
        $this->stream->close();
    }
}
