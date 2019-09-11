<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Writer;

use GpsLab\Component\Sitemap\Writer\Exception\CompressionLevelException;
use GpsLab\Component\Sitemap\Writer\GzipFileWriter;
use GpsLab\Component\Sitemap\Writer\State\Exception\WriterStateException;
use PHPUnit\Framework\TestCase;

class GzipFileWriterTest extends TestCase
{
    /**
     * @var GzipFileWriter
     */
    private $writer;

    /**
     * @var string
     */
    private $filename;

    protected function setUp(): void
    {
        if (!extension_loaded('zlib')) {
            $this->markTestSkipped('The Zlib PHP extension is not loaded.');
        }

        $this->writer = new GzipFileWriter(9);
        $this->filename = tempnam(sys_get_temp_dir(), 'sitemap');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }

    public function testAlreadyStarted(): void
    {
        $this->writer->start($this->filename);

        $this->expectException(WriterStateException::class);
        $this->writer->start($this->filename);
    }

    public function testFinishNotStarted(): void
    {
        $this->expectException(WriterStateException::class);
        $this->writer->finish();
    }

    public function testAlreadyFinished(): void
    {
        $this->writer->start($this->filename);
        $this->writer->finish();

        $this->expectException(WriterStateException::class);
        $this->writer->finish();
    }

    public function testAppendNotStarted(): void
    {
        $this->expectException(WriterStateException::class);
        $this->writer->append('foo');
    }

    public function testAppendAfterFinish(): void
    {
        $this->writer->start($this->filename);
        $this->writer->finish();

        $this->expectException(WriterStateException::class);
        $this->writer->append('foo');
    }

    /**
     * @return array
     */
    public function getCompressionLevels(): array
    {
        return [
            [0, false],
            [-1, false],
            [10, false],
            [11, false],
        ];
    }

    /**
     * @dataProvider getCompressionLevels
     *
     * @param int $compression_level
     */
    public function testInvalidCompressionLevel(int $compression_level): void
    {
        $this->expectException(CompressionLevelException::class);
        new GzipFileWriter($compression_level);
    }

    public function testWrite(): void
    {
        $this->writer->start($this->filename);
        $this->writer->append('foo');
        $this->writer->append('bar');
        $this->writer->finish();

        $handle = gzopen($this->filename, 'rb9');
        $content = gzread($handle, 128);
        gzclose($handle);

        self::assertEquals('foobar', $content);
    }
}
