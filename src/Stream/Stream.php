<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream;

use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Url\Url;

interface Stream
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
     * @param Url $url
     *
     * @throws StreamStateException
     */
    public function push(Url $url): void;
}
