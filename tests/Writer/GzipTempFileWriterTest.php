<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Writer;

use GpsLab\Component\Sitemap\Writer\Exception\CompressionLevelException;
use GpsLab\Component\Sitemap\Writer\Exception\StateException;
use GpsLab\Component\Sitemap\Writer\GzipTempFileWriter;

final class GzipTempFileWriterTest extends TestCase
{
    /**
     * @var GzipTempFileWriter
     */
    private $writer;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $filename2;

    protected function setUp(): void
    {
        if (!extension_loaded('zlib')) {
            $this->markTestSkipped('The Zlib PHP extension is not loaded.');
        }

        $this->writer = new GzipTempFileWriter();
        $this->filename = $this->tempnam(sys_get_temp_dir(), 'sitemap');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }

        if ($this->filename2 && file_exists($this->filename2)) {
            unlink($this->filename2);
        }
    }

    public function testAlreadyStarted(): void
    {
        $this->writer->start($this->filename);

        $this->expectException(StateException::class);
        $this->filename2 = $this->tempnam(sys_get_temp_dir(), 'sitemap');
        $this->writer->start($this->filename2);
    }

    public function testFinishNotStarted(): void
    {
        $this->expectException(StateException::class);
        $this->writer->finish();
    }

    public function testAlreadyFinished(): void
    {
        $this->writer->start($this->filename);
        $this->writer->finish();

        $this->expectException(StateException::class);
        $this->writer->finish();
    }

    public function testAppendNotStarted(): void
    {
        $this->expectException(StateException::class);
        $this->writer->append('foo');
    }

    public function testAppendAfterFinish(): void
    {
        $this->writer->start($this->filename);
        $this->writer->finish();

        $this->expectException(StateException::class);
        $this->writer->append('foo');
    }

    /**
     * @return int[][]
     */
    public function getInvalidCompressionLevels(): array
    {
        return [[0], [-1], [10], [11]];
    }

    /**
     * @dataProvider getInvalidCompressionLevels
     *
     * @param int $compression_level
     */
    public function testInvalidCompressionLevel(int $compression_level): void
    {
        $this->expectException(CompressionLevelException::class);
        new GzipTempFileWriter($compression_level);
    }

    /**
     * @return int[][]
     */
    public function getCompressionLevels(): array
    {
        return [[1], [2], [3], [4], [5], [6], [7], [8], [9]];
    }

    /**
     * @dataProvider getCompressionLevels
     *
     * @param int $compression_level
     */
    public function testWrite(int $compression_level): void
    {
        $this->writer = new GzipTempFileWriter($compression_level);
        $this->writer->start($this->filename);
        $this->writer->append('foo');
        $this->writer->append('bar');
        $this->writer->finish();

        $handle = $this->gzopen($this->filename, sprintf('rb%s', $compression_level));
        $content = gzread($handle, 128);
        gzclose($handle);

        self::assertEquals('foobar', $content);
    }
}
