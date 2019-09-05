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

class OutputWriter implements Writer
{
    /**
     * @param string $filename
     */
    public function open(string $filename): void
    {
        // do nothing
    }

    /**
     * @param string $content
     */
    public function append(string $content): void
    {
        echo $content;
        flush();
    }

    public function finish(): void
    {
        // do nothing
    }
}
