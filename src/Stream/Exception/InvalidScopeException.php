<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\Exception;

use GpsLab\Component\Sitemap\Exception\InvalidArgumentException;

final class InvalidScopeException extends InvalidArgumentException
{
    /**
     * @param string $scope
     *
     * @return self
     */
    public static function invalid(string $scope): self
    {
        return new self(sprintf('You specify "%s" the invalid scope.', $scope));
    }

    /**
     * @param string $scope
     *
     * @return self
     */
    public static function pathOnly(string $scope): self
    {
        return new self(sprintf('The scope must not contain URL query or fragment, got "%s" instead.', $scope));
    }

    /**
     * @param string $scope
     *
     * @return self
     */
    public static function notPath(string $scope): self
    {
        return new self(sprintf('The scope must contain a URL path to folder, got "%s" instead.', $scope));
    }
}
