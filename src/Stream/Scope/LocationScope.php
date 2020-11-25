<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\Scope;

use GpsLab\Component\Sitemap\Location;
use GpsLab\Component\Sitemap\Stream\Exception\InvalidScopeException;

final class LocationScope
{
    /**
     * @var string
     */
    private $scope;

    /**
     * @param string $scope
     */
    public function __construct(string $scope)
    {
        if (filter_var($scope, FILTER_VALIDATE_URL) === false) {
            throw InvalidScopeException::invalid($scope);
        }

        // expected: https://example.com/some/path/
        if (parse_url($scope, PHP_URL_QUERY) || parse_url($scope, PHP_URL_FRAGMENT)) {
            throw InvalidScopeException::pathOnly($scope);
        }

        if (substr($scope, -1, 1) !== '/') {
            throw InvalidScopeException::notPath($scope);
        }

        $this->scope = $scope;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @param Location $location
     *
     * @return bool
     */
    public function inScope(Location $location): bool
    {
        return strpos((string) $location, $this->scope) === 0;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->scope;
    }
}
