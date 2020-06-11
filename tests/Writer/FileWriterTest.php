<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Writer;

use GpsLab\Component\Sitemap\Writer\FileWriter;
use GpsLab\Component\Sitemap\Writer\State\Exception\WriterStateException;

final class FileWriterTest extends TestCase
{
    /**
     * @var FileWriter
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
        $this->writer = new FileWriter();
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

        $this->expectException(WriterStateException::class);
        $this->filename2 = $this->tempnam(sys_get_temp_dir(), 'sitemap');
        $this->writer->start($this->filename2);
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

    public function testWrite(): void
    {
        $this->writer->start($this->filename);
        $this->writer->append('foo');
        $this->writer->append('bar');
        $this->writer->finish();

        self::assertEquals('foobar', $this->file_get_contents($this->filename));
    }
}
