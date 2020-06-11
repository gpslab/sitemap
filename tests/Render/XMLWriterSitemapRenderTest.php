<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Render;

use GpsLab\Component\Sitemap\Render\XMLWriterSitemapRender;
use GpsLab\Component\Sitemap\Url\ChangeFrequency;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\TestCase;

final class XMLWriterSitemapRenderTest extends TestCase
{
    /**
     * @var string
     */
    private const WEB_PATH = 'https://example.com';

    /**
     * @var XMLWriterSitemapRender
     */
    private $render;

    protected function setUp(): void
    {
        $this->render = new XMLWriterSitemapRender(self::WEB_PATH);
    }

    /**
     * @return array<int, array<int, string|bool>>
     */
    public function getValidating(): array
    {
        return [
            [
                false,
                '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
            ],
            [
                true,
                '<urlset'.
                ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'.
                ' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9'.
                ' http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"'.
                ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.
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
        $render = new XMLWriterSitemapRender(self::WEB_PATH, $validating);
        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.$start_teg.PHP_EOL;

        self::assertEquals($expected, $render->start());
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testDoubleStart(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapRender(self::WEB_PATH, $validating);
        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.$start_teg.PHP_EOL;

        self::assertEquals($expected, $render->start());
        self::assertEquals($expected, $render->start());
    }

    public function testEndNotStarted(): void
    {
        self::assertEquals('</urlset>'.PHP_EOL, $this->render->end());
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testStartEnd(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapRender(self::WEB_PATH, $validating);
        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
            '</urlset>'.PHP_EOL
        ;

        self::assertEquals($expected, $render->start().$render->end());
    }

    /**
     * @return Url[][]
     */
    public function getUrls(): array
    {
        return [
            [new Url('/')],
            [new Url('/', new \DateTimeImmutable('-1 day'))],
            [new Url('/', null, ChangeFrequency::WEEKLY)],
            [new Url('/', null, null, 10)],
            [new Url('/', null, ChangeFrequency::WEEKLY, 10)],
            [new Url('/', new \DateTimeImmutable('-1 day'), null, 10)],
            [new Url('/', new \DateTimeImmutable('-1 day'), ChangeFrequency::WEEKLY, null)],
            [new Url('/', new \DateTimeImmutable('-1 day'), ChangeFrequency::WEEKLY, 10)],
        ];
    }

    /**
     * @dataProvider getUrls
     *
     * @param Url $url
     */
    public function testAddUrlInNotStarted(Url $url): void
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
            $expected .= '<priority>'.number_format($url->getPriority() / 10, 1).'</priority>';
        }
        $expected .= '</url>';

        self::assertEquals($expected, $this->render->url($url));
    }

    /**
     * @dataProvider getUrls
     *
     * @param Url $url
     */
    public function testAddUrlInNotStartedUseIndent(Url $url): void
    {
        $render = new XMLWriterSitemapRender(self::WEB_PATH, false, true);

        $expected = ' <url>'.PHP_EOL;
        $expected .= '  <loc>'.htmlspecialchars(self::WEB_PATH.$url->getLocation()).'</loc>'.PHP_EOL;
        if ($url->getLastModify()) {
            $expected .= '  <lastmod>'.$url->getLastModify()->format('c').'</lastmod>'.PHP_EOL;
        }
        if ($url->getChangeFrequency()) {
            $expected .= '  <changefreq>'.$url->getChangeFrequency().'</changefreq>'.PHP_EOL;
        }
        if ($url->getPriority()) {
            $expected .= '  <priority>'.number_format($url->getPriority() / 10, 1).'</priority>'.PHP_EOL;
        }
        $expected .= ' </url>'.PHP_EOL;

        self::assertEquals($expected, $render->url($url));
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testUrl(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapRender(self::WEB_PATH, $validating);
        $url = new Url(
            '/',
            new \DateTimeImmutable('-1 day'),
            ChangeFrequency::WEEKLY,
            10
        );

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
                '<url>'.
                    '<loc>'.htmlspecialchars(self::WEB_PATH.$url->getLocation()).'</loc>'.
                    '<lastmod>'.$url->getLastModify()->format('c').'</lastmod>'.
                    '<changefreq>'.$url->getChangeFrequency().'</changefreq>'.
                    '<priority>'.number_format($url->getPriority() / 10, 1).'</priority>'.
                '</url>'.
            '</urlset>'.PHP_EOL
        ;

        self::assertEquals($expected, $render->start().$render->url($url).$render->end());
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testUrlUseIndent(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapRender(self::WEB_PATH, $validating, true);
        $url = new Url(
            '/',
            new \DateTimeImmutable('-1 day'),
            ChangeFrequency::WEEKLY,
            10
        );

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
            ' <url>'.PHP_EOL.
            '  <loc>'.htmlspecialchars(self::WEB_PATH.$url->getLocation()).'</loc>'.PHP_EOL.
            '  <lastmod>'.$url->getLastModify()->format('c').'</lastmod>'.PHP_EOL.
            '  <changefreq>'.$url->getChangeFrequency().'</changefreq>'.PHP_EOL.
            '  <priority>'.number_format($url->getPriority() / 10, 1).'</priority>'.PHP_EOL.
            ' </url>'.PHP_EOL.
            '</urlset>'.PHP_EOL
        ;

        self::assertEquals($expected, $render->start().$render->url($url).$render->end());
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testStreamRender(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapRender(self::WEB_PATH, $validating);
        $url1 = new Url(
            '/',
            new \DateTimeImmutable('-1 day'),
            ChangeFrequency::WEEKLY,
            10
        );
        $url2 = new Url(
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

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
                '<url>'.
                    '<loc>'.htmlspecialchars(self::WEB_PATH.$url1->getLocation()).'</loc>'.
                    '<lastmod>'.$url1->getLastModify()->format('c').'</lastmod>'.
                    '<changefreq>'.$url1->getChangeFrequency().'</changefreq>'.
                    '<priority>'.number_format($url1->getPriority() / 10, 1).'</priority>'.
                '</url>'.
                '<url>'.
                    '<loc>'.htmlspecialchars(self::WEB_PATH.$url2->getLocation()).'</loc>'.
                    '<lastmod>'.$url2->getLastModify()->format('c').'</lastmod>'.
                    '<changefreq>'.$url2->getChangeFrequency().'</changefreq>'.
                    '<priority>'.number_format($url2->getPriority() / 10, 1).'</priority>'.
                '</url>'.
            '</urlset>'.PHP_EOL
        ;

        self::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testStreamRenderUseIndent(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapRender(self::WEB_PATH, $validating, true);
        $url1 = new Url(
            '/',
            new \DateTimeImmutable('-1 day'),
            ChangeFrequency::WEEKLY,
            10
        );
        $url2 = new Url(
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

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
            ' <url>'.PHP_EOL.
            '  <loc>'.htmlspecialchars(self::WEB_PATH.$url1->getLocation()).'</loc>'.PHP_EOL.
            '  <lastmod>'.$url1->getLastModify()->format('c').'</lastmod>'.PHP_EOL.
            '  <changefreq>'.$url1->getChangeFrequency().'</changefreq>'.PHP_EOL.
            '  <priority>'.number_format($url1->getPriority() / 10, 1).'</priority>'.PHP_EOL.
            ' </url>'.PHP_EOL.
            ' <url>'.PHP_EOL.
            '  <loc>'.htmlspecialchars(self::WEB_PATH.$url2->getLocation()).'</loc>'.PHP_EOL.
            '  <lastmod>'.$url2->getLastModify()->format('c').'</lastmod>'.PHP_EOL.
            '  <changefreq>'.$url2->getChangeFrequency().'</changefreq>'.PHP_EOL.
            '  <priority>'.number_format($url2->getPriority() / 10, 1).'</priority>'.PHP_EOL.
            ' </url>'.PHP_EOL.
            '</urlset>'.PHP_EOL
        ;

        self::assertEquals($expected, $actual);
    }
}
