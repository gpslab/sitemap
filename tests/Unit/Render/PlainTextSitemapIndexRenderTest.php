<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Unit\Render;

use GpsLab\Component\Sitemap\Render\PlainTextSitemapIndexRender;
use PHPUnit\Framework\TestCase;

class PlainTextSitemapIndexRenderTest extends TestCase
{
    /**
     * @var PlainTextSitemapIndexRender
     */
    private $render;

    protected function setUp(): void
    {
        $this->render = new PlainTextSitemapIndexRender();
    }

    public function testStart(): void
    {
        $expected = '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        self::assertEquals($expected, $this->render->start());
    }

    public function testEnd(): void
    {
        $expected = '</sitemapindex>'.PHP_EOL;

        self::assertEquals($expected, $this->render->end());
    }

    public function testSitemap(): void
    {
        $filename = 'https://example.com/sitemap1.xml';

        $expected = '<sitemap>'.
            '<loc>'.$filename.'</loc>'.
        '</sitemap>';

        self::assertEquals($expected, $this->render->sitemap($filename));
    }

    public function testSitemapWithLastMod(): void
    {
        $filename = 'https://example.com/sitemap1.xml';
        $last_mod = new \DateTimeImmutable('-1 day');

        $expected = '<sitemap>'.
            '<loc>'.$filename.'</loc>'.
            ($last_mod ? sprintf('<lastmod>%s</lastmod>', $last_mod->format('c')) : '').
        '</sitemap>';

        self::assertEquals($expected, $this->render->sitemap($filename, $last_mod));
    }
}
