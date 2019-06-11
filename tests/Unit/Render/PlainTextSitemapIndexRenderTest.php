<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
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

    /**
     * @var string
     */
    private $host = 'https://example.com';

    protected function setUp(): void
    {
        $this->render = new PlainTextSitemapIndexRender($this->host);
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
        $filename = '/sitemap1.xml';

        $expected = '<sitemap>'.
            '<loc>'.$this->host.$filename.'</loc>'.
        '</sitemap>';

        self::assertEquals($expected, $this->render->sitemap($filename));
    }

    public function testSitemapWithLastMod(): void
    {
        $filename = '/sitemap1.xml';
        $last_mod = new \DateTimeImmutable('-1 day');

        $expected = '<sitemap>'.
            '<loc>'.$this->host.$filename.'</loc>'.
            ($last_mod ? sprintf('<lastmod>%s</lastmod>', $last_mod->format('c')) : '').
        '</sitemap>';

        self::assertEquals($expected, $this->render->sitemap($filename, $last_mod));
    }
}
