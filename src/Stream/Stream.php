<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream;

use GpsLab\Component\Sitemap\Url\Url;

interface Stream
{
    public function open(): void;

    public function close(): void;

    /**
     * @param Url $url
     */
    public function push(Url $url): void;
}
