<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url;

use GpsLab\Component\Sitemap\Location;
use GpsLab\Component\Sitemap\Url\Exception\InvalidLanguageException;

final class Language
{
    /**
     * Use the x-default tag for unmatched languages.
     *
     * The reserved value x-default is used when no other language/region matches the user's browser setting.
     * This value is optional, but recommended, as a way for you to control the page when no languages match.
     * A good use is to target your site's homepage where there is a clickable map that enables the user to select
     * their country.
     */
    public const UNMATCHED_LANGUAGE = 'x-default';

    /**
     * @var string
     */
    private $language;

    /**
     * @var Location
     */
    private $location;

    /**
     * @param string          $language
     * @param Location|string $location
     *
     * @throws InvalidLanguageException
     */
    public function __construct(string $language, $location)
    {
        // language in ISO 639-1 and optionally a region in ISO 3166-1 Alpha 2
        if ($language !== self::UNMATCHED_LANGUAGE && !preg_match('/^[a-z]{2}([-_][a-z]{2})?$/i', $language)) {
            throw InvalidLanguageException::invalid($language);
        }

        $this->language = $language;
        $this->location = $location instanceof Location ? $location : new Location($location);
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return Location
     */
    public function getLocation(): Location
    {
        return $this->location;
    }
}
