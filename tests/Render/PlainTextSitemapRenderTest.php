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

use GpsLab\Component\Sitemap\Render\PlainTextSitemapRender;
use GpsLab\Component\Sitemap\Url\ChangeFreq;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\TestCase;

class PlainTextSitemapRenderTest extends TestCase
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
        $render = new PlainTextSitemapRender($validating);
        $expected = '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.$start_teg;

        self::assertEquals($expected, $render->start());
    }

    public function testEnd(): void
    {
        $expected = '</urlset>'.PHP_EOL;

        self::assertEquals($expected, $this->render->end());
    }

    public function testUrl(): void
    {
        $url = new Url(
            'https://example.com/',
            new \DateTimeImmutable('-1 day'),
            ChangeFreq::WEEKLY,
            '1.0'
        );

        $expected = '<url>'.
            '<loc>'.htmlspecialchars($url->getLocation()).'</loc>'.
            '<lastmod>'.$url->getLastModify()->format('c').'</lastmod>'.
            '<changefreq>'.$url->getChangeFreq().'</changefreq>'.
            '<priority>'.$url->getPriority().'</priority>'.
            '</url>'
        ;

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

        $expected = '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.
            $start_teg.
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
}
