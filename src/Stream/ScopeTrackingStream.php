<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream;

use GpsLab\Component\Sitemap\Stream\Exception\InvalidScopeException;
use GpsLab\Component\Sitemap\Stream\Exception\OutOfScopeException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\Scope\LocationScope;
use GpsLab\Component\Sitemap\Url\Url;

final class ScopeTrackingStream implements Stream
{
    /**
     * @var Stream
     */
    private $wrapped_stream;

    /**
     * @var LocationScope
     */
    private $scope;

    /**
     * @param Stream $wrapped_stream
     * @param string $scope
     *
     * @throws InvalidScopeException
     */
    public function __construct(Stream $wrapped_stream, string $scope)
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
     * @param Url $url
     *
     * @throws StreamStateException
     */
    public function push(Url $url): void
    {
        if (!$this->scope->inScope($url->getLocation())) {
            throw OutOfScopeException::outOf((string) $url->getLocation(), (string) $this->scope);
        }

        $this->wrapped_stream->push($url);
    }
}
