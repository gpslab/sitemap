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

use GpsLab\Component\Sitemap\Writer\Exception\FileAccessException;

class TempFileWriter implements Writer
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
     */
    public function open(string $filename): void
    {
        $this->filename = $filename;
        $this->tmp_filename = tempnam(sys_get_temp_dir(), 'sitemap');
        $this->handle = @fopen($this->tmp_filename, 'wb');

        if ($this->handle === false) {
            throw FileAccessException::notWritable($this->tmp_filename);
        }
    }

    /**
     * @param string $content
     */
    public function append(string $content): void
    {
        fwrite($this->handle, $content);
    }

    public function finish(): void
    {
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
