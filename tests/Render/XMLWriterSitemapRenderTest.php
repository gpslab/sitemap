<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Render;

use GpsLab\Component\Sitemap\Render\XMLWriterSitemapRender;
use GpsLab\Component\Sitemap\Url\ChangeFreq;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\TestCase;

class XMLWriterSitemapRenderTest extends TestCase
{
    /**
     * @var XMLWriterSitemapRender
     */
    private $render;

    protected function setUp(): void
    {
        $this->render = new XMLWriterSitemapRender();
    }

    /**
     * @return array
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
        $render = new XMLWriterSitemapRender($validating);
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
        $render = new XMLWriterSitemapRender($validating);
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
        $render = new XMLWriterSitemapRender($validating);
        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
            '</urlset>'.PHP_EOL
        ;

        self::assertEquals($expected, $render->start().$render->end());
    }

    public function testAddUrlInNotStarted(): void
    {
        $url = new Url(
            'https://example.com/',
            new \DateTimeImmutable('-1 day'),
            ChangeFreq::YEARLY,
            '0.1'
        );

        $expected =
            '<url>'.
                '<loc>'.htmlspecialchars($url->getLocation()).'</loc>'.
                '<lastmod>'.$url->getLastModify()->format('c').'</lastmod>'.
                '<changefreq>'.$url->getChangeFreq().'</changefreq>'.
                '<priority>'.$url->getPriority().'</priority>'.
            '</url>'
        ;

        self::assertEquals($expected, $this->render->url($url));
    }

    public function testAddUrlInNotStartedUseIndent(): void
    {
        $render = new XMLWriterSitemapRender(false, true);
        $url = new Url(
            'https://example.com/',
            new \DateTimeImmutable('-1 day'),
            ChangeFreq::YEARLY,
            '0.1'
        );

        $expected =
            ' <url>'.PHP_EOL.
            '  <loc>'.htmlspecialchars($url->getLocation()).'</loc>'.PHP_EOL.
            '  <lastmod>'.$url->getLastModify()->format('c').'</lastmod>'.PHP_EOL.
            '  <changefreq>'.$url->getChangeFreq().'</changefreq>'.PHP_EOL.
            '  <priority>'.$url->getPriority().'</priority>'.PHP_EOL.
            ' </url>'.PHP_EOL
        ;

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
        $render = new XMLWriterSitemapRender($validating);
        $url = new Url(
            'https://example.com/',
            new \DateTimeImmutable('-1 day'),
            ChangeFreq::YEARLY,
            '0.1'
        );

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
                '<url>'.
                    '<loc>'.htmlspecialchars($url->getLocation()).'</loc>'.
                    '<lastmod>'.$url->getLastModify()->format('c').'</lastmod>'.
                    '<changefreq>'.$url->getChangeFreq().'</changefreq>'.
                    '<priority>'.$url->getPriority().'</priority>'.
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
        $render = new XMLWriterSitemapRender($validating, true);
        $url = new Url(
            'https://example.com/sitemap1.xml',
            new \DateTimeImmutable('-1 day'),
            ChangeFreq::YEARLY,
            '0.1'
        );

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
            ' <url>'.PHP_EOL.
            '  <loc>'.htmlspecialchars($url->getLocation()).'</loc>'.PHP_EOL.
            '  <lastmod>'.$url->getLastModify()->format('c').'</lastmod>'.PHP_EOL.
            '  <changefreq>'.$url->getChangeFreq().'</changefreq>'.PHP_EOL.
            '  <priority>'.$url->getPriority().'</priority>'.PHP_EOL.
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
        $render = new XMLWriterSitemapRender($validating);
        $url1 = new Url(
            'https://example.com/',
            new \DateTimeImmutable('-1 day'),
            ChangeFreq::WEEKLY,
            '1.0'
        );
        $url2 = new Url(
            'https://example.com/about',
            new \DateTimeImmutable('-1 month'),
            ChangeFreq::YEARLY,
            '0.9'
        );

        $actual = $render->start().$render->url($url1);
        // render end string right after render first URL and before another URLs
        // this is necessary to calculate the size of the sitemap in bytes
        $end = $render->end();
        $actual .= $render->url($url2).$end;

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
                '<url>'.
                    '<loc>'.htmlspecialchars($url1->getLocation()).'</loc>'.
                    '<lastmod>'.$url1->getLastModify()->format('c').'</lastmod>'.
                    '<changefreq>'.$url1->getChangeFreq().'</changefreq>'.
                    '<priority>'.$url1->getPriority().'</priority>'.
                '</url>'.
                '<url>'.
                    '<loc>'.htmlspecialchars($url2->getLocation()).'</loc>'.
                    '<lastmod>'.$url2->getLastModify()->format('c').'</lastmod>'.
                    '<changefreq>'.$url2->getChangeFreq().'</changefreq>'.
                    '<priority>'.$url2->getPriority().'</priority>'.
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
        $render = new XMLWriterSitemapRender($validating, true);
        $url1 = new Url(
            'https://example.com/',
            new \DateTimeImmutable('-1 day'),
            ChangeFreq::WEEKLY,
            '1.0'
        );
        $url2 = new Url(
            'https://example.com/about',
            new \DateTimeImmutable('-1 month'),
            ChangeFreq::YEARLY,
            '0.9'
        );

        $actual = $render->start().$render->url($url1);
        // render end string right after render first URL and before another URLs
        // this is necessary to calculate the size of the sitemap in bytes
        $end = $render->end();
        $actual .= $render->url($url2).$end;

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
            ' <url>'.PHP_EOL.
            '  <loc>'.htmlspecialchars($url1->getLocation()).'</loc>'.PHP_EOL.
            '  <lastmod>'.$url1->getLastModify()->format('c').'</lastmod>'.PHP_EOL.
            '  <changefreq>'.$url1->getChangeFreq().'</changefreq>'.PHP_EOL.
            '  <priority>'.$url1->getPriority().'</priority>'.PHP_EOL.
            ' </url>'.PHP_EOL.
            ' <url>'.PHP_EOL.
            '  <loc>'.htmlspecialchars($url2->getLocation()).'</loc>'.PHP_EOL.
            '  <lastmod>'.$url2->getLastModify()->format('c').'</lastmod>'.PHP_EOL.
            '  <changefreq>'.$url2->getChangeFreq().'</changefreq>'.PHP_EOL.
            '  <priority>'.$url2->getPriority().'</priority>'.PHP_EOL.
            ' </url>'.PHP_EOL.
            '</urlset>'.PHP_EOL
        ;

        self::assertEquals($expected, $actual);
    }
}
