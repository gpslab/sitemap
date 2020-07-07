<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url;

use GpsLab\Component\Sitemap\Exception\InvalidLocationException;
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
     * @var string
     */
    private $location;

    /**
     * @var bool
     */
    private $local_location;

    /**
     * @param string $language
     * @param string $location
     *
     * @throws InvalidLanguageException
     */
    public function __construct(string $language, string $location)
    {
        // language in ISO 639-1 and optionally a region in ISO 3166-1 Alpha 2
        if ($language !== self::UNMATCHED_LANGUAGE && !preg_match('/^[a-z]{2}([-_][a-z]{2})?$/i', $language)) {
            throw InvalidLanguageException::invalid($language);
        }

        // localization pages do not need to be in the same domain
        $this->local_location = !$location || in_array($location[0], ['/', '?', '#'], true);
        $validate_url = $this->local_location ? sprintf('https://example.com%s', $location) : $location;

        if (filter_var($validate_url, FILTER_VALIDATE_URL) === false) {
            throw InvalidLocationException::invalid($location);
        }

        $this->language = $language;
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @return bool
     */
    public function isLocalLocation(): bool
    {
        return $this->local_location;
    }
}
