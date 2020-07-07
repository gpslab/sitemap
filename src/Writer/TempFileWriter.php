<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Writer;

use GpsLab\Component\Sitemap\Writer\Exception\FileAccessException;
use GpsLab\Component\Sitemap\Writer\Exception\StateException;

final class TempFileWriter implements Writer
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

        $tmp_filename = tempnam(sys_get_temp_dir(), 'sitemap');

        if ($tmp_filename === false) {
            throw FileAccessException::tempnam(sys_get_temp_dir(), 'sitemap');
        }

        $handle = @fopen($tmp_filename, 'wb');

        if ($handle === false) {
            throw FileAccessException::notWritable($this->tmp_filename);
        }

        $this->filename = $filename;
        $this->tmp_filename = $tmp_filename;
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

        fwrite($this->handle, $content);
    }

    /**
     * @throws StateException
     * @throws FileAccessException
     */
    public function finish(): void
    {
        if (!$this->handle) {
            throw StateException::notStarted();
        }

        fclose($this->handle);

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
