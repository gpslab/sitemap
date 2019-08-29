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

    /**
     * @return array
     */
    public function getValidating(): array
    {
        return [
            [
                false,
                '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
            ],
            [
                true,
                '<sitemapindex'.
                ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'.
                ' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9'.
                ' http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd"'.
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
        $render = new PlainTextSitemapIndexRender($validating);
        $expected = '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.$start_teg;

        self::assertEquals($expected, $render->start());
    }

    public function testEnd(): void
    {
        $expected = '</sitemapindex>'.PHP_EOL;

        self::assertEquals($expected, $this->render->end());
    }

    public function testSitemap(): void
    {
        $path = 'http://example.com/sitemap1.xml';

        $expected = '<sitemap>'.
            '<loc>'.$path.'</loc>'.
        '</sitemap>';

        self::assertEquals($expected, $this->render->sitemap($path));
    }

    /**
     * @return array
     */
    public function getLastMod(): array
    {
        return [
            [new \DateTime('-1 day')],
            [new \DateTimeImmutable('-1 day')],
        ];
    }

    /**
     * @dataProvider getLastMod
     *
     * @param \DateTimeInterface $last_modify
     */
    public function testSitemapWithLastMod(\DateTimeInterface $last_modify): void
    {
        $path = 'http://example.com/sitemap1.xml';

        $expected = '<sitemap>'.
            '<loc>'.$path.'</loc>'.
            ($last_modify ? sprintf('<lastmod>%s</lastmod>', $last_modify->format('c')) : '').
        '</sitemap>';

        self::assertEquals($expected, $this->render->sitemap($path, $last_modify));
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testStreamRender(bool $validating, string $start_teg): void
    {
        $render = new PlainTextSitemapIndexRender($validating);
        $path1 = 'http://example.com/sitemap.xml';
        $path2 = 'http://example.com/sitemap.xml';

        $actual = $render->start().$render->sitemap($path1);
        // render end string right after render first Sitemap and before another Sitemaps
        // this is necessary to calculate the size of the sitemap index in bytes
        $end = $render->end();
        $actual .= $render->sitemap($path2).$end;

        $expected = '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.
            $start_teg.
                '<sitemap>'.
                    '<loc>'.$path1.'</loc>'.
                '</sitemap>'.
                '<sitemap>'.
                    '<loc>'.$path2.'</loc>'.
                '</sitemap>'.
            '</sitemapindex>'.PHP_EOL
        ;

        self::assertEquals($expected, $actual);
    }
}
