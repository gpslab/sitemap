<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Writer;

use GpsLab\Component\Sitemap\Writer\Exception\CompressionEncodingException;
use GpsLab\Component\Sitemap\Writer\Exception\CompressionLevelException;
use GpsLab\Component\Sitemap\Writer\Exception\CompressionMemoryException;
use GpsLab\Component\Sitemap\Writer\Exception\CompressionWindowException;
use GpsLab\Component\Sitemap\Writer\Exception\DeflateCompressionException;
use GpsLab\Component\Sitemap\Writer\Exception\ExtensionNotLoadedException;
use GpsLab\Component\Sitemap\Writer\Exception\FileAccessException;
use GpsLab\Component\Sitemap\Writer\Exception\StateException;

final class DeflateTempFileWriter implements Writer
{
    /**
     * @var resource|null
     */
    private $handle;

    /**
     * @var resource|null
     */
    private $context;

    /**
     * @var int
     */
    private $encoding;

    /**
     * @var int
     */
    private $level;

    /**
     * @var int
     */
    private $memory;

    /**
     * @var int
     */
    private $window;

    /**
     * @var string
     */
    private $filename = '';

    /**
     * @var string
     */
    private $tmp_filename = '';

    /**
     * @param int $encoding
     * @param int $level
     * @param int $memory
     * @param int $window
     *
     * @throws ExtensionNotLoadedException
     * @throws CompressionEncodingException
     */
    public function __construct(
        int $encoding = ZLIB_ENCODING_GZIP,
        int $level = -1,
        int $memory = 9,
        int $window = 15
    ) {
        if (!extension_loaded('zlib')) {
            throw ExtensionNotLoadedException::zlib();
        }

        if (!in_array($encoding, [ZLIB_ENCODING_RAW, ZLIB_ENCODING_GZIP, ZLIB_ENCODING_DEFLATE], true)) {
            throw CompressionEncodingException::invalid($encoding);
        }

        if ($level < -1 || $level > 9) {
            throw CompressionLevelException::invalid($level, -1, 9);
        }

        if ($memory < 1 || $memory > 9) {
            throw CompressionMemoryException::invalid($memory, 1, 9);
        }

        if ($window < 8 || $window > 15) {
            throw CompressionWindowException::invalid($window, 8, 15);
        }

        $this->encoding = $encoding;
        $this->level = $level;
        $this->memory = $memory;
        $this->window = $window;
    }

    /**
     * @param string $filename
     *
     * @throws StateException
     * @throws FileAccessException
     * @throws DeflateCompressionException
     */
    public function start(string $filename): void
    {
        if ($this->handle) {
            throw StateException::alreadyStarted();
        }

        $tmp_filename = tempnam(sys_get_temp_dir(), 'sitemap');

        if ($tmp_filename === false) {
            throw FileAccessException::tempnam(sys_get_temp_dir(), 'sitemap');
        }

        $handle = fopen($tmp_filename, 'wb');

        if ($handle === false) {
            throw FileAccessException::notWritable($this->tmp_filename);
        }

        $context = deflate_init($this->encoding, [
            'level' => $this->level,
            'memory' => $this->memory,
            'window' => $this->window,
        ]);

        if ($context === false) {
            throw DeflateCompressionException::failedInit();
        }

        $this->filename = $filename;
        $this->tmp_filename = $tmp_filename;
        $this->handle = $handle;
        $this->context = $context;
    }

    /**
     * @param string $content
     *
     * @throws StateException
     * @throws DeflateCompressionException
     */
    public function append(string $content): void
    {
        if (!$this->handle) {
            throw StateException::notReady();
        }

        $data = deflate_add($this->context, $content, ZLIB_NO_FLUSH);

        if ($data === false) {
            throw DeflateCompressionException::failedAdd($content);
        }

        fwrite($this->handle, $data);
    }

    /**
     * @throws StateException
     * @throws DeflateCompressionException
     * @throws FileAccessException
     */
    public function finish(): void
    {
        if (!$this->handle) {
            throw StateException::notStarted();
        }

        $data = deflate_add($this->context, '', ZLIB_FINISH);

        if ($data === false) {
            throw DeflateCompressionException::failedFinish();
        }

        fwrite($this->handle, $data);
        fclose($this->handle);

        // move the sitemap file from the temporary directory to the target
        if (!rename($this->tmp_filename, $this->filename)) {
            unlink($this->tmp_filename);

            throw FileAccessException::failedOverwrite($this->tmp_filename, $this->filename);
        }

        $this->handle = $this->context = null;
        $this->filename = $this->tmp_filename = '';
    }
}
