<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Writer;

use GpsLab\Component\Sitemap\Writer\DeflateFileWriter;
use GpsLab\Component\Sitemap\Writer\Exception\CompressionEncodingException;
use GpsLab\Component\Sitemap\Writer\Exception\CompressionLevelException;
use GpsLab\Component\Sitemap\Writer\Exception\CompressionMemoryException;
use GpsLab\Component\Sitemap\Writer\Exception\CompressionWindowException;
use GpsLab\Component\Sitemap\Writer\Exception\StateException;

final class DeflateFileWriterTest extends TestCase
{
    private const ENCODINGS = [ZLIB_ENCODING_RAW, ZLIB_ENCODING_GZIP, ZLIB_ENCODING_DEFLATE];

    private const LEVELS = [-1, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9];

    private const MEMORIES = [1, 2, 3, 4, 5, 6, 7, 8, 9];

    private const WINDOWS = [8, 9, 10, 11, 12, 13, 14, 15];

    /**
     * @var DeflateFileWriter
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

        $this->writer = new DeflateFileWriter();
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
    public function getInvalidCompressionEncoding(): array
    {
        return [[0], [-1], [10]];
    }

    /**
     * @dataProvider getInvalidCompressionEncoding
     *
     * @param int $encoding
     */
    public function testInvalidCompressionEncoding(int $encoding): void
    {
        $this->expectException(CompressionEncodingException::class);
        new DeflateFileWriter($encoding);
    }

    /**
     * @return int[][]
     */
    public function getCompressionLevels(): array
    {
        return [[-2], [10], [11]];
    }

    /**
     * @dataProvider getCompressionLevels
     *
     * @param int $level
     */
    public function testInvalidCompressionLevel(int $level): void
    {
        $this->expectException(CompressionLevelException::class);
        new DeflateFileWriter(ZLIB_ENCODING_GZIP, $level);
    }

    /**
     * @return int[][]
     */
    public function getCompressionMemory(): array
    {
        return [[0], [-1], [10], [11]];
    }

    /**
     * @dataProvider getCompressionMemory
     *
     * @param int $memory
     */
    public function testInvalidCompressionMemory(int $memory): void
    {
        $this->expectException(CompressionMemoryException::class);
        new DeflateFileWriter(ZLIB_ENCODING_GZIP, -1, $memory);
    }

    /**
     * @return int[][]
     */
    public function getCompressionWindow(): array
    {
        return [[0], [1], [7], [16], [17]];
    }

    /**
     * @dataProvider getCompressionWindow
     *
     * @param int $window
     */
    public function testInvalidCompressionWindow(int $window): void
    {
        $this->expectException(CompressionWindowException::class);
        new DeflateFileWriter(ZLIB_ENCODING_GZIP, -1, 9, $window);
    }

    /**
     * @return int[][]
     */
    public function getCompressionOptions(): array
    {
        $params = [];
        foreach (self::ENCODINGS as $encoding) {
            foreach (self::LEVELS as $level) {
                foreach (self::MEMORIES as $memory) {
                    foreach (self::WINDOWS as $window) {
                        // 256-byte windows are broken
                        // https://github.com/madler/zlib/issues/171
                        if ($encoding !== ZLIB_ENCODING_DEFLATE && $window !== 8) {
                            $params[] = [$encoding, $level, $memory, $window];
                        }
                    }
                }
            }
        }

        return $params;
    }

    /**
     * @dataProvider getCompressionOptions
     *
     * @param int $encoding
     * @param int $level
     * @param int $memory
     * @param int $window
     */
    public function testWrite(int $encoding, int $level, int $memory, int $window): void
    {
        $this->writer = new DeflateFileWriter($encoding, $level, $memory, $window);
        $this->writer->start($this->filename);
        $this->writer->append('foo');
        $this->writer->append('bar');
        $this->writer->finish();

        $context = $this->inflate_init($encoding, [
            'level' => $level,
            'memory' => $memory,
            'window' => $window,
        ]);
        $content = inflate_add($context, $this->file_get_contents($this->filename));

        self::assertEquals('foobar', $content);
    }

    /**
     * @return int[][]
     */
    public function getBrokenWindowCompressionOptions(): array
    {
        $params = [];
        foreach (self::LEVELS as $level) {
            foreach (self::MEMORIES as $memory) {
                $params[] = [$level, $memory];
            }
        }

        return $params;
    }

    /**
     * @dataProvider getBrokenWindowCompressionOptions
     *
     * @param int $level
     * @param int $memory
     */
    public function testBrokenWindow(int $level, int $memory): void
    {
        // 256-byte windows are broken
        // https://github.com/madler/zlib/issues/171

        $expected_window = 8;
        $actual_window = 9;

        $this->writer = new DeflateFileWriter(ZLIB_ENCODING_DEFLATE, $level, $memory, $expected_window);
        $this->writer->start($this->filename);
        $this->writer->append('foo');
        $this->writer->append('bar');
        $this->writer->finish();

        $context = $this->inflate_init(ZLIB_ENCODING_DEFLATE, [
            'level' => $level,
            'memory' => $memory,
            'window' => $actual_window,
        ]);
        $content = inflate_add($context, $this->file_get_contents($this->filename));

        self::assertEquals('foobar', $content);
    }
}
