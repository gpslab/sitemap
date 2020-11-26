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
use GpsLab\Component\Sitemap\Stream\Exception\InvalidScopeException;
use GpsLab\Component\Sitemap\Stream\Exception\OutOfScopeException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\Scope\LocationScope;

final class ScopeTrackingIndexStream implements IndexStream
{
    /**
     * @var IndexStream
     */
    private $wrapped_stream;

    /**
     * @var LocationScope
     */
    private $scope;

    /**
     * @param IndexStream $wrapped_stream
     * @param string      $scope
     *
     * @throws InvalidScopeException
     */
    public function __construct(IndexStream $wrapped_stream, string $scope)
    {
        $this->wrapped_stream = $wrapped_stream;
        $this->scope = new LocationScope($scope);
    }

    /**
     * @throws StreamStateException
     */
    public function open(): void
    {
        $this->wrapped_stream->open();
    }

    /**
     * @throws StreamStateException
     */
    public function close(): void
    {
        $this->wrapped_stream->close();
    }

    /**
     * @param Sitemap $sitemap
     *
     * @throws StreamStateException
     */
    public function pushSitemap(Sitemap $sitemap): void
    {
        if (!$this->scope->inScope($sitemap->getLocation())) {
            throw OutOfScopeException::outOf((string) $sitemap->getLocation(), (string) $this->scope);
        }

        $this->wrapped_stream->pushSitemap($sitemap);
    }
}
