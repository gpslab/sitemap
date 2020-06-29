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

final class FileWriter implements Writer
{
    /**
     * @var resource|null
     */
    private $handle;

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

        $handle = @fopen($filename, 'wb');

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

        fwrite($this->handle, $content);
    }

    /**
     * @throws StateException
     */
    public function finish(): void
    {
        if (!$this->handle) {
            throw StateException::notStarted();
        }

        fclose($this->handle);
        $this->handle = null;
    }
}
