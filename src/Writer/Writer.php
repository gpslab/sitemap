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

interface Writer
{
    /**
     * @param string $filename
     */
    public function open(string $filename): void;

    /**
     * @param string $content
     */
    public function write(string $content): void;

    public function close(): void;
}
