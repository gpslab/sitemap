<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Functional\Stream;

use GpsLab\Component\Sitemap\Render\PlainTextSitemapIndexRender;
use GpsLab\Component\Sitemap\Render\PlainTextSitemapRender;
use GpsLab\Component\Sitemap\Stream\RenderFileStream;
use GpsLab\Component\Sitemap\Stream\RenderIndexFileStream;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\TestCase;

class RenderIndexFileStreamTest extends TestCase
{
    /**
     * @var RenderIndexFileStream
     */
    private $stream;

    /**
     * @var string
     */
    private $host = 'https://example.com/';

    /**
     * @var string
     */
    private $filename = '';

    protected function setUp(): void
    {
        $this->filename = sys_get_temp_dir().'/sitemap.xml';
        $this->tearDown();

        $index_render = new PlainTextSitemapIndexRender();
        $render = new PlainTextSitemapRender();
        $substream = new RenderFileStream($render, $this->filename);
        $this->stream = new RenderIndexFileStream($index_render, $substream, $this->host, $this->filename);
    }

    protected function tearDown(): void
    {
        $files = [
            $this->filename,
            $this->getFilenameOfIndex($this->filename, 1),
            $this->getFilenameOfIndex($this->filename, 2),
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testEmpty(): void
    {
        // filling
        $this->stream->open();
        $this->stream->close();

        // test result
        self::assertFileExists($this->filename);
        self::assertFileExists($this->getFilenameOfIndex($this->filename, 1));
        self::assertFileNotExists($this->getFilenameOfIndex($this->filename, 2));
    }

    public function testOverflow(): void
    {
        // filling
        $this->stream->open();
        for ($i = 0; $i <= RenderFileStream::LINKS_LIMIT; ++$i) {
            $this->stream->push(new Url('/'));
        }
        $this->stream->close();

        // test result
        self::assertFileExists($this->filename);
        self::assertFileExists($this->getFilenameOfIndex($this->filename, 1));
        self::assertFileExists($this->getFilenameOfIndex($this->filename, 2));
    }

    /**
     * @param string $filename
     * @param int    $index
     *
     * @return string
     */
    private function getFilenameOfIndex(string $filename, int $index): string
    {
        // use explode() for correct add index
        // sitemap.xml -> sitemap1.xml
        // sitemap.xml.gz -> sitemap1.xml.gz

        list($filename, $extension) = explode('.', basename($filename), 2);

        return sprintf('%s/%s%s.%s', dirname($this->filename), $filename, $index, $extension);
    }
}
