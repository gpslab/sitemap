<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Render;

use GpsLab\Component\Sitemap\Render\PlainTextSitemapRender;
use GpsLab\Component\Sitemap\Url\ChangeFrequency;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\TestCase;

final class PlainTextSitemapRenderTest extends TestCase
{
    /**
     * @var PlainTextSitemapRender
     */
    private $render;

    protected function setUp(): void
    {
        $this->render = new PlainTextSitemapRender();
    }

    /**
     * @return array<int, array<int, string|bool>>
     */
    public function getValidating(): array
    {
        return [
            [
                false,
                '<urlset'.
                ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.
                ' xmlns:xhtml="http://www.w3.org/1999/xhtml"'.
                '>',
            ],
            [
                true,
                '<urlset'.
                ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'.
                ' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9'.
                ' http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"'.
                ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.
                ' xmlns:xhtml="http://www.w3.org/1999/xhtml"'.
                '>',
            ],
        ];
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testStart(bool $validating, string $start_teg): void
    {
        $render = new PlainTextSitemapRender($validating);
        $expected = '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.$start_teg;

        self::assertEquals($expected, $render->start());
    }

    public function testEnd(): void
    {
        $expected = '</urlset>'.PHP_EOL;

        self::assertEquals($expected, $this->render->end());
    }

    /**
     * @return Url[][]
     */
    public function getUrls(): array
    {
        return [
            [Url::create('https://example.com/')],
            [Url::create('https://example.com/', new \DateTimeImmutable('-1 day'))],
            [Url::create('https://example.com/', null, ChangeFrequency::WEEKLY)],
            [Url::create('https://example.com/', null, null, 10)],
            [Url::create('https://example.com/', null, ChangeFrequency::WEEKLY, 10)],
            [Url::create('https://example.com/', new \DateTimeImmutable('-1 day'), null, 10)],
            [Url::create('https://example.com/', new \DateTimeImmutable('-1 day'), ChangeFrequency::WEEKLY, null)],
            [Url::create('https://example.com/', new \DateTimeImmutable('-1 day'), ChangeFrequency::WEEKLY, 10)],
            [Url::create('https://example.com/?foo=\'bar\'&baz=">"&zaz=<')], // test escaping
            [Url::create(
                'https://example.com/english/page.html',
                new \DateTimeImmutable('-1 day'),
                ChangeFrequency::WEEKLY,
                10,
                [
                    'de' => 'https://de.example.com/page.html',
                    'de-ch' => 'https://example.com/schweiz-deutsch/page.html',
                    'en' => 'https://example.com/english/page.html',
                ]),
            ],
        ];
    }

    /**
     * @dataProvider getUrls
     *
     * @param Url $url
     */
    public function testUrl(Url $url): void
    {
        $expected = '<url>';
        $expected .= '<loc>'.htmlspecialchars((string) $url->getLocation()).'</loc>';

        if ($url->getLastModify()) {
            $expected .= '<lastmod>'.$url->getLastModify()->format('c').'</lastmod>';
        }

        if ($url->getChangeFrequency()) {
            $expected .= '<changefreq>'.$url->getChangeFrequency().'</changefreq>';
        }

        if ($url->getPriority()) {
            $expected .= '<priority>'.$url->getPriority().'</priority>';
        }

        foreach ($url->getLanguages() as $language) {
            $location = htmlspecialchars((string) $language->getLocation());
            $expected .= '<xhtml:link rel="alternate" hreflang="'.$language->getLanguage().'" href="'.$location.'"/>';
        }

        $expected .= '</url>';

        self::assertEquals($expected, $this->render->url($url));
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testStreamRender(bool $validating, string $start_teg): void
    {
        $render = new PlainTextSitemapRender($validating);
        $url1 = Url::create(
            'https://example.com/',
            new \DateTimeImmutable('-1 day'),
            ChangeFrequency::WEEKLY,
            10
        );
        $url2 = Url::create(
            'https://example.com/about',
            new \DateTimeImmutable('-1 month'),
            ChangeFrequency::YEARLY,
            9
        );

        $actual = $render->start().$render->url($url1);
        // render end string right after render first URL and before another URLs
        // this is necessary to calculate the size of the sitemap in bytes
        $end = $render->end();
        $actual .= $render->url($url2).$end;

        $expected = '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.
            $start_teg.
                '<url>'.
                    '<loc>'.htmlspecialchars((string) $url1->getLocation()).'</loc>'.
                    '<lastmod>'.$url1->getLastModify()->format('c').'</lastmod>'.
                    '<changefreq>'.$url1->getChangeFrequency().'</changefreq>'.
                    '<priority>'.$url1->getPriority().'</priority>'.
                '</url>'.
                '<url>'.
                    '<loc>'.htmlspecialchars((string) $url2->getLocation()).'</loc>'.
                    '<lastmod>'.$url2->getLastModify()->format('c').'</lastmod>'.
                    '<changefreq>'.$url2->getChangeFrequency().'</changefreq>'.
                    '<priority>'.$url2->getPriority().'</priority>'.
                '</url>'.
            '</urlset>'.PHP_EOL
        ;

        self::assertEquals($expected, $actual);
    }
}
