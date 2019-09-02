<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream;

use GpsLab\Component\Sitemap\Limiter;
use GpsLab\Component\Sitemap\Url\Url;

interface Stream
{
    /**
     * @deprecated use Limiter::LINKS_LIMIT.
     */
    public const LINKS_LIMIT = Limiter::LINKS_LIMIT;

    /**
     * @deprecated use Limiter::BYTE_LIMIT.
     */
    public const BYTE_LIMIT = Limiter::BYTE_LIMIT;

    public function open(): void;

    public function close(): void;

    /**
     * @param Url $url
     */
    public function push(Url $url): void;
}
