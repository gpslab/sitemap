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

class FileWriter implements Writer
{
    /**
     * @var resource|null
     */
    private $handle;

    /**
     * @param string $filename
     */
    public function start(string $filename): void
    {
        $this->handle = @fopen($filename, 'wb');

        if ($this->handle === false) {
            throw FileAccessException::notWritable($filename);
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
        $this->handle = null;
    }
}
