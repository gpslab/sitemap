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
use GpsLab\Component\Sitemap\Location;
use GpsLab\Component\Sitemap\Url\ChangeFrequency;
use GpsLab\Component\Sitemap\Url\Exception\InvalidChangeFrequencyException;
use GpsLab\Component\Sitemap\Url\Exception\InvalidLastModifyException;
use GpsLab\Component\Sitemap\Url\Exception\InvalidPriorityException;
use GpsLab\Component\Sitemap\Url\Language;
use GpsLab\Component\Sitemap\Url\Priority;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\TestCase;

final class UrlTest extends TestCase
{
    public function testDefaultUrl(): void
    {
        $location = '';
        $url = Url::create($location);

        self::assertEquals($location, (string) $url->getLocation());
        self::assertNull($url->getLastModify());
        self::assertNull($url->getChangeFrequency());
        self::assertNull($url->getPriority());
        self::assertEmpty($url->getLanguages());
    }

    public function testDefaultSmartUrl(): void
    {
        $location = '';
        $url = Url::createSmart($location);

        $priority = Priority::createByLocation(new Location($location));
        $change_frequency = ChangeFrequency::createByPriority($priority);

        self::assertEquals($location, (string) $url->getLocation());
        self::assertNull($url->getLastModify());
        self::assertEquals($change_frequency, (string) $url->getChangeFrequency());
        self::assertSame($priority, $url->getPriority());
    }

    /**
     * @return array<int, array<int, \DateTimeInterface|string|int>>
     */
    public function getUrls(): array
    {
        return [
            [new \DateTimeImmutable('-10 minutes'), ChangeFrequency::ALWAYS, '1.0'],
            [new \DateTimeImmutable('-1 hour'), ChangeFrequency::HOURLY, '1.0'],
            [new \DateTimeImmutable('-1 day'), ChangeFrequency::DAILY, '0.9'],
            [new \DateTimeImmutable('-1 week'), ChangeFrequency::WEEKLY, '0.5'],
            [new \DateTimeImmutable('-1 month'), ChangeFrequency::MONTHLY, '0.2'],
            [new \DateTimeImmutable('-1 year'), ChangeFrequency::YEARLY, '0.1'],
            [new \DateTimeImmutable('-2 year'), ChangeFrequency::NEVER, '0.0'],
            [new \DateTime('-10 minutes'), ChangeFrequency::ALWAYS, '1.0'],
            [new \DateTime('-1 hour'), ChangeFrequency::HOURLY, '1.0'],
            [new \DateTime('-1 day'), ChangeFrequency::DAILY, '0.9'],
            [new \DateTime('-1 week'), ChangeFrequency::WEEKLY, '0.5'],
            [new \DateTime('-1 month'), ChangeFrequency::MONTHLY, '0.2'],
            [new \DateTime('-1 year'), ChangeFrequency::YEARLY, '0.1'],
            [new \DateTime('-2 year'), ChangeFrequency::NEVER, '0.0'],
        ];
    }

    /**
     * @dataProvider getUrls
     *
     * @param \DateTimeInterface $last_modify
     * @param string             $change_frequency
     * @param string             $priority
     */
    public function testCustomUrl(\DateTimeInterface $last_modify, string $change_frequency, string $priority): void
    {
        $location = '/index.html';

        $url = Url::create($location, $last_modify, $change_frequency, $priority);

        self::assertEquals($location, (string) $url->getLocation());
        self::assertEquals($last_modify, $url->getLastModify());
        self::assertEquals($change_frequency, (string) $url->getChangeFrequency());
        self::assertEquals($priority, (string) $url->getPriority());
    }

    /**
     * @dataProvider getUrls
     *
     * @param \DateTimeInterface $last_modify
     * @param string             $change_frequency
     * @param string             $priority
     */
    public function testCustomSmartUrl(
        \DateTimeInterface $last_modify,
        string $change_frequency,
        string $priority
    ): void {
        $location = '/index.html';

        $url = Url::createSmart($location, $last_modify, $change_frequency, $priority);

        self::assertEquals($location, (string) $url->getLocation());
        self::assertEquals($last_modify, $url->getLastModify());
        self::assertEquals($change_frequency, (string) $url->getChangeFrequency());
        self::assertEquals($priority, (string) $url->getPriority());
    }

    /**
     * @return string[][]
     */
    public function getInvalidLocations(): array
    {
        return [
            ['../'],
            ['index.html'],
            ['&foo=bar'],
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
    public function testInvalidLocation(string $location): void
    {
        $this->expectException(InvalidLocationException::class);

        Url::create($location);
    }

    /**
     * @dataProvider getInvalidLocations
     *
     * @param string $location
     */
    public function testInvalidSmartLocation(string $location): void
    {
        $this->expectException(InvalidLocationException::class);

        Url::createSmart($location);
    }

    /**
     * @return string[][]
     */
    public function getValidLocations(): array
    {
        return [
            [''],
            ['/'],
            ['#about'],
            ['?foo=bar'],
            ['?foo=bar&baz=123'],
            ['/index.html'],
            ['/about/index.html'],
        ];
    }

    /**
     * @dataProvider getValidLocations
     *
     * @param string $location
     */
    public function testValidLocation(string $location): void
    {
        $this->assertEquals($location, (string) (Url::create($location))->getLocation());
    }

    /**
     * @dataProvider getValidLocations
     *
     * @param string $location
     */
    public function testValidSmartLocation(string $location): void
    {
        $this->assertEquals($location, (string) (Url::createSmart($location))->getLocation());
    }

    public function testInvalidLastModify(): void
    {
        $this->expectException(InvalidLastModifyException::class);

        Url::create('/', new \DateTimeImmutable('+1 minutes'));
    }

    public function testInvalidSmartLastModify(): void
    {
        $this->expectException(InvalidLastModifyException::class);

        Url::createSmart('/', new \DateTimeImmutable('+1 minutes'));
    }

    public function testInvalidPriority(): void
    {
        $this->expectException(InvalidPriorityException::class);

        Url::create('/', null, null, 11);
    }

    public function testInvalidSmartPriority(): void
    {
        $this->expectException(InvalidPriorityException::class);

        Url::createSmart('/', null, null, 11);
    }

    public function testInvalidChangeFrequency(): void
    {
        $this->expectException(InvalidChangeFrequencyException::class);

        Url::create('/', null, '');
    }


    public function testInvalidSmartChangeFrequency(): void
    {
        $this->expectException(InvalidChangeFrequencyException::class);

        Url::createSmart('/', null, '');
    }

    public function testGetLanguages(): void
    {
        $languages = [
            'de' => '/deutsch/page.html',
            'de-ch' => '/schweiz-deutsch/page.html',
            'en' => '/english/page.html',
        ];

        $url = Url::create('/english/page.html', null, null, null, $languages);

        self::assertNotEmpty($url->getLanguages());

        $keys = array_keys($languages);

        foreach ($url->getLanguages() as $j => $language) {
            self::assertInstanceOf(Language::class, $language);
            self::assertSame($keys[$j], $language->getLanguage());
            self::assertSame($languages[$keys[$j]], $language->getLocation());
        }
    }

    public function testGetSmartLanguages(): void
    {
        $languages = [
            'de' => '/deutsch/page.html',
            'de-ch' => '/schweiz-deutsch/page.html',
            'en' => '/english/page.html',
        ];

        $url = Url::createSmart('/english/page.html', null, null, null, $languages);

        self::assertNotEmpty($url->getLanguages());

        $keys = array_keys($languages);

        foreach ($url->getLanguages() as $j => $language) {
            self::assertInstanceOf(Language::class, $language);
            self::assertSame($keys[$j], $language->getLanguage());
            self::assertSame($languages[$keys[$j]], $language->getLocation());
        }
    }

    /**
     * @dataProvider getUrls
     *
     * @param \DateTimeInterface $last_modify
     * @param string             $change_frequency
     * @param string             $priority
     */
    public function testCreateLanguageUrls(
        \DateTimeInterface $last_modify,
        string $change_frequency,
        string $priority
    ): void {
        $languages = [
            'de' => '/deutsch/page.html',
            'de-ch' => '/schweiz-deutsch/page.html',
            'en' => '/english/page.html',
        ];
        $external_languages = [
            'de' => 'https://example.de', // should be overwritten from $languages
            'fr' => 'https://example.fr',
        ];
        $expected_locations = array_values($languages);
        $expected_languages = array_replace($external_languages, $languages);

        $urls = Url::createLanguageUrls($languages, $last_modify, $change_frequency, $priority, $external_languages);

        self::assertCount(count($expected_locations), $urls);

        foreach ($urls as $i => $url) {
            self::assertSame($last_modify, $url->getLastModify());
            self::assertSame($change_frequency, (string) $url->getChangeFrequency());
            self::assertSame($priority, (string) $url->getPriority());
            self::assertSame($expected_locations[$i], (string) $url->getLocation());
            self::assertNotEmpty($url->getLanguages());

            $keys = array_keys($expected_languages);
            foreach ($url->getLanguages() as $j => $language) {
                self::assertInstanceOf(Language::class, $language);
                self::assertSame($keys[$j], $language->getLanguage());
                self::assertSame($expected_languages[$keys[$j]], $language->getLocation());
            }
        }
    }

    /**
     * @return string[][][]
     */
    public function getNonUniqueLanguageLocations(): array
    {
        return [
            [
                [
                    'de' => '/deutsch/page.html',
                    'de-ch' => '/schweiz-deutsch/page.html',
                    'en' => '/english/page.html',
                    'x-default' => '/english/page.html',
                ],
                [
                    '/deutsch/page.html',
                    '/schweiz-deutsch/page.html',
                    '/english/page.html',
                ],
            ],
            [
                [
                    'de' => '/deutsch/page.html',
                    'de-ch' => '/schweiz-deutsch/page.html',
                    'x-default' => '/english/page.html', // unmatched language
                ],
                [
                    '/deutsch/page.html',
                    '/schweiz-deutsch/page.html',
                    '/english/page.html',
                ],
            ],
            [
                [
                    'de' => '/deutsch/page.html',
                    'de-ch' => '/schweiz-deutsch/page.html',
                    'en' => '/english/page.html',
                    'en-US' => '/english/page.html',
                    'en-GB' => '/english/page.html',
                ],
                [
                    '/deutsch/page.html',
                    '/schweiz-deutsch/page.html',
                    '/english/page.html',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getNonUniqueLanguageLocations
     *
     * @param array<string, string> $languages
     * @param string[]              $locations
     */
    public function testCreateLanguageUrlsUnique(array $languages, array $locations): void
    {
        $urls = Url::createLanguageUrls($languages);

        self::assertCount(count($locations), $urls);

        foreach ($urls as $i => $url) {
            self::assertSame($locations[$i], (string) $url->getLocation());
            self::assertNotEmpty($url->getLanguages());

            $keys = array_keys($languages);
            foreach ($url->getLanguages() as $j => $language) {
                self::assertInstanceOf(Language::class, $language);
                self::assertSame($keys[$j], $language->getLanguage());
                self::assertSame($languages[$keys[$j]], $language->getLocation());
            }
        }
    }

    /**
     * @return string[][]
     */
    public function getPriorityOfLocations(): array
    {
        return [
            ['/', '1.0'],
            ['/index.html', '0.9'],
            ['/catalog', '0.9'],
            ['/catalog/123', '0.8'],
            ['/catalog/123/article', '0.7'],
            ['/catalog/123/article/456', '0.6'],
            ['/catalog/123/article/456/print', '0.5'],
            ['/catalog/123/subcatalog/789/article/456', '0.4'],
            ['/catalog/123/subcatalog/789/article/456/print', '0.3'],
            ['/catalog/123/subcatalog/789/article/456/print/foo', '0.2'],
            ['/catalog/123/subcatalog/789/article/456/print/foo/bar', '0.1'],
            ['/catalog/123/subcatalog/789/article/456/print/foo/bar/baz', '0.1'],
            ['/catalog/123/subcatalog/789/article/456/print/foo/bar/baz/qux', '0.1'],
        ];
    }

    /**
     * @dataProvider getPriorityOfLocations
     *
     * @param string $location
     * @param string $priority
     */
    public function testSmartPriority(string $location, string $priority): void
    {
        $url = Url::createSmart($location);

        self::assertEquals($location, (string) $url->getLocation());
        self::assertEquals($priority, (string) $url->getPriority());
    }

    /**
     * @return array<int, array<int, \DateTimeInterface|string>>
     */
    public function getChangeFrequencyOfLastModify(): array
    {
        return [
            [new \DateTimeImmutable('-1 year -1 day'), ChangeFrequency::YEARLY],
            [new \DateTimeImmutable('-1 month -1 day'), ChangeFrequency::MONTHLY],
            [new \DateTimeImmutable('-1 week -1 day'), ChangeFrequency::WEEKLY],
            [new \DateTimeImmutable('-10 minutes'), ChangeFrequency::HOURLY],
            [new \DateTime('-1 year -1 day'), ChangeFrequency::YEARLY],
            [new \DateTime('-1 month -1 day'), ChangeFrequency::MONTHLY],
            [new \DateTime('-1 week -1 day'), ChangeFrequency::WEEKLY],
            [new \DateTime('-10 minutes'), ChangeFrequency::HOURLY],
        ];
    }

    /**
     * @dataProvider getChangeFrequencyOfLastModify
     *
     * @param \DateTimeInterface $last_modify
     * @param string             $change_frequency
     */
    public function testSmartChangeFrequencyFromLastMod(
        \DateTimeInterface $last_modify,
        string $change_frequency
    ): void {
        $location = '/';
        $url = Url::createSmart($location, $last_modify);

        self::assertEquals($location, (string) $url->getLocation());
        self::assertEquals($last_modify, $url->getLastModify());
        self::assertEquals($change_frequency, (string) $url->getChangeFrequency());
    }

    /**
     * @return array<int, array<int, int|string>>
     */
    public function getChangeFrequencyOfPriority(): array
    {
        return [
            ['1.0', ChangeFrequency::HOURLY],
            ['0.9', ChangeFrequency::DAILY],
            ['0.8', ChangeFrequency::DAILY],
            ['0.7', ChangeFrequency::WEEKLY],
            ['0.6', ChangeFrequency::WEEKLY],
            ['0.5', ChangeFrequency::WEEKLY],
            ['0.4', ChangeFrequency::MONTHLY],
            ['0.3', ChangeFrequency::MONTHLY],
            ['0.2', ChangeFrequency::YEARLY],
            ['0.1', ChangeFrequency::YEARLY],
            ['0.0', ChangeFrequency::NEVER],
        ];
    }

    /**
     * @dataProvider getChangeFrequencyOfPriority
     *
     * @param string $priority
     * @param string $change_frequency
     */
    public function testSmartChangeFrequencyFromPriority(string $priority, string $change_frequency): void
    {
        $location = '/';
        $url = Url::createSmart($location, null, null, $priority);

        self::assertEquals($location, (string) $url->getLocation());
        self::assertNull($url->getLastModify());
        self::assertEquals($change_frequency, (string) $url->getChangeFrequency());
        self::assertEquals($priority, (string) $url->getPriority());
    }
}
