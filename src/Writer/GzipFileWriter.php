<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Writer;

use GpsLab\Component\Sitemap\Writer\Exception\CompressionLevelException;
use GpsLab\Component\Sitemap\Writer\Exception\ExtensionNotLoadedException;
use GpsLab\Component\Sitemap\Writer\Exception\FileAccessException;
use GpsLab\Component\Sitemap\Writer\State\Exception\WriterStateException;
use GpsLab\Component\Sitemap\Writer\State\WriterState;

class GzipFileWriter implements Writer
{
    /**
     * @var resource|null
     */
    private $handle;

    /**
     * @var int
     */
    private $compression_level;

    /**
     * @var WriterState
     */
    private $state;

    /**
     * @param int $compression_level
     */
    public function __construct(int $compression_level = 9)
    {
        if ($compression_level < 1 || $compression_level > 9) {
            throw CompressionLevelException::invalid($compression_level, 1, 9);
        }

        if (!extension_loaded('zlib')) {
            throw ExtensionNotLoadedException::zlib();
        }

        $this->compression_level = $compression_level;
        $this->state = new WriterState();
    }

    /**
     * @param string $filename
     */
    public function start(string $filename): void
    {
        $this->state->start();
        $mode = 'wb'.$this->compression_level;
        $this->handle = @gzopen($filename, $mode);

        if ($this->handle === false) {
            throw FileAccessException::notWritable($filename);
        }
    }

    /**
     * @param string $content
     */
    public function append(string $content): void
    {
        if (!$this->state->isReady()) {
            throw WriterStateException::notReady();
        }

        gzwrite($this->handle, $content);
    }

    public function finish(): void
    {
        $this->state->finish();
        gzclose($this->handle);
        $this->handle = null;
    }
}
