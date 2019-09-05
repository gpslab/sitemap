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

class GzipFileWriter implements Writer
{
    /**
     * @var resource|null
     */
    private $handle;

    /**
     * @var int
     */
    private $compression_level = 9;

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
        gzwrite($this->handle, $content);
    }

    public function finish(): void
    {
        gzclose($this->handle);
        $this->handle = null;
    }
}
