<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream;

use GpsLab\Component\Sitemap\Url\Url;

interface Stream
{
    public const LINKS_LIMIT = 50000;

    public const BYTE_LIMIT = 52428800; // 50 Mb

    public function open(): void;

    public function close(): void;

    /**
     * @param Url $url
     */
    public function push(Url $url): void;
}
