<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream;

use GpsLab\Component\Sitemap\Sitemap\Sitemap;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;

interface IndexStream
{
    /**
     * @throws StreamStateException
     */
    public function open(): void;

    /**
     * @throws StreamStateException
     */
    public function close(): void;

    /**
     * @param Sitemap $sitemap
     *
     * @throws StreamStateException
     */
    public function pushSitemap(Sitemap $sitemap): void;
}
