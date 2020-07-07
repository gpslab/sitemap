<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Writer;

use GpsLab\Component\Sitemap\Writer\Exception\StateException;

interface Writer
{
    /**
     * @param string $filename
     *
     * @throws StateException
     */
    public function start(string $filename): void;

    /**
     * @param string $content
     *
     * @throws StateException
     */
    public function append(string $content): void;

    /**
     * @throws StateException
     */
    public function finish(): void;
}
