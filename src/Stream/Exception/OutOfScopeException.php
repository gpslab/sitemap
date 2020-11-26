<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream\Exception;

final class OutOfScopeException extends \DomainException
{
    /**
     * @param string $location
     * @param string $scope
     *
     * @return OutOfScopeException
     */
    public static function outOf(string $location, string $scope): self
    {
        return new self(sprintf('The location "%s" is out of scope "%s".', $location, $scope));
    }
}
