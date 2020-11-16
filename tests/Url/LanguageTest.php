<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Url;

use GpsLab\Component\Sitemap\Exception\InvalidLocationException;
use GpsLab\Component\Sitemap\Url\Exception\InvalidLanguageException;
use GpsLab\Component\Sitemap\Url\Language;
use PHPUnit\Framework\TestCase;

final class LanguageTest extends TestCase
{
    /**
     * @return string[][]
     */
    public function getInvalidLanguages(): array
    {
        return [
            ['deutsch'],
            ['schweiz-deutsch'],
            ['a'],
            ['abc'],
            ['a1'],
            ['de=ch'],
            ['de-c'],
            ['de-chw'],
            ['de-ch1'],
        ];
    }

    /**
     * @dataProvider getInvalidLanguages
     *
     * @param string $language
     */
    public function testInvalidLanguages(string $language): void
    {
        $this->expectException(InvalidLanguageException::class);

        new Language($language, 'https://example.com');
    }

    /**
     * @return string[][]
     */
    public function getInvalidLocations(): array
    {
        return [
            [''],
            ['/'],
            ['../'],
            ['index.html'],
            ['?foo=bar'],
            ['&foo=bar'],
            ['#'],
            ['№'],
            ['@'],
            ['\\'],
        ];
    }

    /**
     * @dataProvider getInvalidLocations
     *
     * @param string $location
     */
    public function testInvalidLocations(string $location): void
    {
        $this->expectException(InvalidLocationException::class);

        new Language('de', $location);
    }

    /**
     * @return array<int, array<int, string|bool>>
     */
    public function getLanguage(): array
    {
        $result = [];
        $languages = ['x-default'];
        $locations = [
            'https://example.com',
            'https://example.com/',
            'https://example.com#about',
            'https://example.com?foo=bar',
            'https://example.com?foo=bar&baz=123',
            'https://example.com/index.html',
            'https://example.com/about/index.html',
        ];

        // build list $languages
        foreach (['de', 'De', 'dE', 'DE'] as $lang) {
            $languages[] = $lang;

            foreach (['-', '_'] as $separator) {
                foreach (['ch', 'Ch', 'cH', 'CH'] as $region) {
                    $languages[] = $lang.$separator.$region;
                }
            }
        }

        // build local locations
        foreach ($locations as $location) {
            foreach ($languages as $language) {
                $result[] = [$language, $location, true];
            }
        }

        return $result;
    }

    /**
     * @dataProvider getLanguage
     *
     * @param string $language
     * @param string $location
     */
    public function testLanguage(string $language, string $location): void
    {
        $lang = new Language($language, $location);
        self::assertSame($language, $lang->getLanguage());
        self::assertSame($location, (string) $lang->getLocation());
    }
}
