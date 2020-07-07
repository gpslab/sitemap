<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Writer;

use GpsLab\Component\Sitemap\Writer\Exception\CompressionLevelException;
use GpsLab\Component\Sitemap\Writer\Exception\ExtensionNotLoadedException;
use GpsLab\Component\Sitemap\Writer\Exception\FileAccessException;
use GpsLab\Component\Sitemap\Writer\Exception\StateException;

final class GzipFileWriter implements Writer
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
     * @param int $compression_level
     *
     * @throws ExtensionNotLoadedException
     * @throws CompressionLevelException
     */
    public function __construct(int $compression_level = 9)
    {
        if (!extension_loaded('zlib')) {
            throw ExtensionNotLoadedException::zlib();
        }

        if ($compression_level < 1 || $compression_level > 9) {
            throw CompressionLevelException::invalid($compression_level, 1, 9);
        }

        $this->compression_level = $compression_level;
    }

    /**
     * @param string $filename
     *
     * @throws StateException
     * @throws FileAccessException
     */
    public function start(string $filename): void
    {
        if ($this->handle) {
            throw StateException::alreadyStarted();
        }

        $mode = 'wb'.$this->compression_level;
        $handle = @gzopen($filename, $mode);

        if ($handle === false) {
            throw FileAccessException::notWritable($filename);
        }

        $this->handle = $handle;
    }

    /**
     * @param string $content
     *
     * @throws StateException
     */
    public function append(string $content): void
    {
        if (!$this->handle) {
            throw StateException::notReady();
        }

        gzwrite($this->handle, $content);
    }

    /**
     * @throws StateException
     */
    public function finish(): void
    {
        if (!$this->handle) {
            throw StateException::notStarted();
        }

        gzclose($this->handle);
        $this->handle = null;
    }
}
