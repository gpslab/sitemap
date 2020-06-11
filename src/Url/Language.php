<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url;

use GpsLab\Component\Sitemap\Url\Exception\InvalidLanguageException;
use GpsLab\Component\Sitemap\Url\Exception\InvalidLocationException;

final class Language
{
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
     */
    public function __construct(string $language, string $location)
    {
        // language in ISO 639-1 and optionally a region in ISO 3166-1 Alpha 2
        if (!preg_match('/^[a-z]{2}([-_][a-z]{2})?$/i', $language)) {
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
