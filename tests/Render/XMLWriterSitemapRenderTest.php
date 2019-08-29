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

    /**
     * @var string
     */
    private $web_path = 'https://example.com';

    protected function setUp(): void
    {
        $this->render = new XMLWriterSitemapRender($this->web_path);
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
        $render = new XMLWriterSitemapRender($this->web_path, $validating);
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
        $render = new XMLWriterSitemapRender($this->web_path, $validating);
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
        $render = new XMLWriterSitemapRender($this->web_path, $validating);
        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
            '</urlset>'.PHP_EOL
        ;

        self::assertEquals($expected, $render->start().$render->end());
    }

    /**
     * @return array
     */
    public function getUrls(): array
    {
        return [
            [new Url('/')],
            [new Url('/', new \DateTimeImmutable('-1 day'))],
            [new Url('/', null, ChangeFreq::WEEKLY)],
            [new Url('/', null, null, '1.0')],
            [new Url('/', null, ChangeFreq::WEEKLY, '1.0')],
            [new Url('/', new \DateTimeImmutable('-1 day'), null, '1.0')],
            [new Url('/', new \DateTimeImmutable('-1 day'), ChangeFreq::WEEKLY, null)],
            [new Url('/', new \DateTimeImmutable('-1 day'), ChangeFreq::WEEKLY, '1.0')],
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
        $expected .= '<loc>'.htmlspecialchars($this->web_path.$url->getLocation()).'</loc>';
        if ($url->getLastModify()) {
            $expected .= '<lastmod>'.$url->getLastModify()->format('c').'</lastmod>';
        }
        if ($url->getChangeFreq()) {
            $expected .= '<changefreq>'.$url->getChangeFreq().'</changefreq>';
        }
        if ($url->getPriority()) {
            $expected .= '<priority>'.$url->getPriority().'</priority>';
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
        $render = new XMLWriterSitemapRender($this->web_path, false, true);

        $expected = ' <url>'.PHP_EOL;
        $expected .= '  <loc>'.htmlspecialchars($this->web_path.$url->getLocation()).'</loc>'.PHP_EOL;
        if ($url->getLastModify()) {
            $expected .= '  <lastmod>'.$url->getLastModify()->format('c').'</lastmod>'.PHP_EOL;
        }
        if ($url->getChangeFreq()) {
            $expected .= '  <changefreq>'.$url->getChangeFreq().'</changefreq>'.PHP_EOL;
        }
        if ($url->getPriority()) {
            $expected .= '  <priority>'.$url->getPriority().'</priority>'.PHP_EOL;
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
        $render = new XMLWriterSitemapRender($this->web_path, $validating);
        $url = new Url(
            '/',
            new \DateTimeImmutable('-1 day'),
            ChangeFreq::WEEKLY,
            '1.0'
        );

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
                '<url>'.
                    '<loc>'.htmlspecialchars($this->web_path.$url->getLocation()).'</loc>'.
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
        $render = new XMLWriterSitemapRender($this->web_path, $validating, true);
        $url = new Url(
            '/',
            new \DateTimeImmutable('-1 day'),
            ChangeFreq::WEEKLY,
            '1.0'
        );

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
            ' <url>'.PHP_EOL.
            '  <loc>'.htmlspecialchars($this->web_path.$url->getLocation()).'</loc>'.PHP_EOL.
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
        $render = new XMLWriterSitemapRender($this->web_path, $validating);
        $url1 = new Url(
            '/',
            new \DateTimeImmutable('-1 day'),
            ChangeFreq::WEEKLY,
            '1.0'
        );
        $url2 = new Url(
            '/about',
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
                    '<loc>'.htmlspecialchars($this->web_path.$url1->getLocation()).'</loc>'.
                    '<lastmod>'.$url1->getLastModify()->format('c').'</lastmod>'.
                    '<changefreq>'.$url1->getChangeFreq().'</changefreq>'.
                    '<priority>'.$url1->getPriority().'</priority>'.
                '</url>'.
                '<url>'.
                    '<loc>'.htmlspecialchars($this->web_path.$url2->getLocation()).'</loc>'.
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
        $render = new XMLWriterSitemapRender($this->web_path, $validating, true);
        $url1 = new Url(
            '/',
            new \DateTimeImmutable('-1 day'),
            ChangeFreq::WEEKLY,
            '1.0'
        );
        $url2 = new Url(
            '/about',
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
            '  <loc>'.htmlspecialchars($this->web_path.$url1->getLocation()).'</loc>'.PHP_EOL.
            '  <lastmod>'.$url1->getLastModify()->format('c').'</lastmod>'.PHP_EOL.
            '  <changefreq>'.$url1->getChangeFreq().'</changefreq>'.PHP_EOL.
            '  <priority>'.$url1->getPriority().'</priority>'.PHP_EOL.
            ' </url>'.PHP_EOL.
            ' <url>'.PHP_EOL.
            '  <loc>'.htmlspecialchars($this->web_path.$url2->getLocation()).'</loc>'.PHP_EOL.
            '  <lastmod>'.$url2->getLastModify()->format('c').'</lastmod>'.PHP_EOL.
            '  <changefreq>'.$url2->getChangeFreq().'</changefreq>'.PHP_EOL.
            '  <priority>'.$url2->getPriority().'</priority>'.PHP_EOL.
            ' </url>'.PHP_EOL.
            '</urlset>'.PHP_EOL
        ;

        self::assertEquals($expected, $actual);
    }
}
