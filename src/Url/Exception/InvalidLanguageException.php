<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Exception;

use GpsLab\Component\Sitemap\Exception\InvalidArgumentException;

final class InvalidLanguageException extends InvalidArgumentException
{
    /**
     * @param string $location
     *
     * @return self
     */
    public static function invalid(string $location): self
    {
        return new self(sprintf(
            'You specify "%s" the invalid language. '.
            'The language should be in ISO 639-1 and optionally with a region in ISO 3166-1 Alpha 2. '.
            'Fore example: en, de-AT, nl_BE.',
            $location
        ));
    }
}
