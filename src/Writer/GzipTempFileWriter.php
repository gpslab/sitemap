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

class GzipTempFileWriter implements Writer
{
    /**
     * @var resource|null
     */
    private $handle;

    /**
     * @var string
     */
    private $filename = '';

    /**
     * @var string
     */
    private $tmp_filename = '';

    /**
     * @var int
     */
    private $compression_level;

    /**
     * @param int $compression_level
     */
    public function __construct(int $compression_level)
    {
        if ($compression_level < 1 || $compression_level > 9) {
            throw CompressionLevelException::invalid($compression_level, 1, 9);
        }

        if (!extension_loaded('zlib')) {
            throw ExtensionNotLoadedException::zlib();
        }

        $this->compression_level = $compression_level;
    }

    /**
     * @param string $filename
     */
    public function open(string $filename): void
    {
        $this->filename = $filename;
        $this->tmp_filename = tempnam(sys_get_temp_dir(), 'sitemap');
        $mode = 'wb'.$this->compression_level;
        $this->handle = @gzopen($this->tmp_filename, $mode);

        if ($this->handle === false) {
            throw FileAccessException::notWritable($this->tmp_filename);
        }
    }

    /**
     * @param string $content
     */
    public function append(string $content): void
    {
        gzwrite($this->handle, $content);
    }

    public function finish(): void
    {
        gzclose($this->handle);

        // move the sitemap file from the temporary directory to the target
        if (!rename($this->tmp_filename, $this->filename)) {
            unlink($this->tmp_filename);

            throw FileAccessException::failedOverwrite($this->tmp_filename, $this->filename);
        }

        $this->handle = null;
        $this->filename = '';
        $this->tmp_filename = '';
    }
}
