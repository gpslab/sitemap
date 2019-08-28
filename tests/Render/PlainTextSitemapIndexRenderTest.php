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
        $path = '/sitemap1.xml';

        $expected = '<sitemap>'.
            '<loc>'.$this->host.$path.'</loc>'.
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
        $path = '/sitemap1.xml';

        $expected = '<sitemap>'.
            '<loc>'.$this->host.$path.'</loc>'.
            ($last_modify ? sprintf('<lastmod>%s</lastmod>', $last_modify->format('c')) : '').
        '</sitemap>';

        self::assertEquals($expected, $this->render->sitemap($path, $last_modify));
    }

    public function testStreamRender(): void
    {
        $path1 = '/sitemap1.xml';
        $path2 = '/sitemap1.xml';

        $actual = $this->render->start().$this->render->sitemap($path1);
        // render end string right after render first Sitemap and before another Sitemaps
        // this is necessary to calculate the size of the sitemap index in bytes
        $end = $this->render->end();
        $actual .= $this->render->sitemap($path2).$end;

        $expected = '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.
                '<sitemap>'.
                    '<loc>'.$this->host.$path1.'</loc>'.
                '</sitemap>'.
                '<sitemap>'.
                    '<loc>'.$this->host.$path2.'</loc>'.
                '</sitemap>'.
            '</sitemapindex>'.PHP_EOL
        ;

        self::assertEquals($expected, $actual);
    }
}
