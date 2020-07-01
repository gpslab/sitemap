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
use GpsLab\Component\Sitemap\Url\Exception\LocationTooLongException;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\TestCase;

final class PlainTextSitemapRenderTest extends TestCase
{
    private const WEB_PATH = 'https://example.com';

    /**
     * @var PlainTextSitemapRender
     */
    private $render;

    protected function setUp(): void
    {
        $this->render = new PlainTextSitemapRender(self::WEB_PATH);
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
                ' xmlns:xhtml="https://www.w3.org/1999/xhtml"'.
                '>',
            ],
            [
                true,
                '<urlset'.
                ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'.
                ' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9'.
                ' http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"'.
                ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.
                ' xmlns:xhtml="https://www.w3.org/1999/xhtml"'.
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
        $render = new PlainTextSitemapRender(self::WEB_PATH, $validating);
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
            [Url::create('/')],
            [Url::create('/', new \DateTimeImmutable('-1 day'))],
            [Url::create('/', null, ChangeFrequency::WEEKLY)],
            [Url::create('/', null, null, 10)],
            [Url::create('/', null, ChangeFrequency::WEEKLY, 10)],
            [Url::create('/', new \DateTimeImmutable('-1 day'), null, 10)],
            [Url::create('/', new \DateTimeImmutable('-1 day'), ChangeFrequency::WEEKLY, null)],
            [Url::create('/', new \DateTimeImmutable('-1 day'), ChangeFrequency::WEEKLY, 10)],
            [Url::create('/?foo=\'bar\'&baz=">"&zaz=<')], // test escaping
            [Url::create('/english/page.html', new \DateTimeImmutable('-1 day'), ChangeFrequency::WEEKLY, 10, [
                'de' => 'https://de.example.com/page.html',
                'de-ch' => '/schweiz-deutsch/page.html',
                'en' => '/english/page.html',
            ])],
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
        $expected .= '<loc>'.htmlspecialchars(self::WEB_PATH.$url->getLocation()).'</loc>';

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
            // alternate URLs do not need to be in the same domain
            if ($language->isLocalLocation()) {
                $location = htmlspecialchars(self::WEB_PATH.$language->getLocation());
            } else {
                $location = $language->getLocation();
            }

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
        $render = new PlainTextSitemapRender(self::WEB_PATH, $validating);
        $url1 = Url::create(
            '/',
            new \DateTimeImmutable('-1 day'),
            ChangeFrequency::WEEKLY,
            10
        );
        $url2 = Url::create(
            '/about',
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
                    '<loc>'.htmlspecialchars(self::WEB_PATH.$url1->getLocation()).'</loc>'.
                    '<lastmod>'.$url1->getLastModify()->format('c').'</lastmod>'.
                    '<changefreq>'.$url1->getChangeFrequency().'</changefreq>'.
                    '<priority>'.$url1->getPriority().'</priority>'.
                '</url>'.
                '<url>'.
                    '<loc>'.htmlspecialchars(self::WEB_PATH.$url2->getLocation()).'</loc>'.
                    '<lastmod>'.$url2->getLastModify()->format('c').'</lastmod>'.
                    '<changefreq>'.$url2->getChangeFrequency().'</changefreq>'.
                    '<priority>'.$url2->getPriority().'</priority>'.
                '</url>'.
            '</urlset>'.PHP_EOL
        ;

        self::assertEquals($expected, $actual);
    }

    public function testLocationTooLong(): void
    {
        $this->expectException(LocationTooLongException::class);

        $location_max_length = 2047;

        $web_path = str_repeat('f', ceil($location_max_length / 2));
        $location = str_repeat('f', ceil($location_max_length / 2) + 1 /* overflow */);

        $render = new PlainTextSitemapRender($web_path);
        $render->url(Url::create($location));
    }
}
