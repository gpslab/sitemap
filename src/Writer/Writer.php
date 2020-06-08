<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Writer;

interface Writer
{
    /**
     * @param string $filename
     */
    public function start(string $filename): void;

    /**
     * @param string $content
     */
    public function append(string $content): void;

    public function finish(): void;
}
