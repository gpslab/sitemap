<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Unit\Render;

use GpsLab\Component\Sitemap\Render\PlainTextSitemapRender;
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

    public function testStart(): void
    {
        $expected = '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        self::assertEquals($expected, $this->render->start());
    }

    public function testEnd(): void
    {
        $expected = '</urlset>'.PHP_EOL;

        self::assertEquals($expected, $this->render->end());
    }

    public function testUrl(): void
    {
        $url = new Url(
            'https://example.com/sitemap1.xml',
            new \DateTimeImmutable('-1 day'),
            Url::CHANGE_FREQ_YEARLY,
            '0.1'
        );

        $expected = '<url>'.
            '<loc>'.htmlspecialchars($url->getLoc()).'</loc>'.
            '<lastmod>'.$url->getLastMod()->format('Y-m-d').'</lastmod>'.
            '<changefreq>'.$url->getChangeFreq().'</changefreq>'.
            '<priority>'.$url->getPriority().'</priority>'.
            '</url>'
        ;

        self::assertEquals($expected, $this->render->url($url));
    }
}
