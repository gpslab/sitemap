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

use GpsLab\Component\Sitemap\Render\XMLWriterSitemapIndexRender;
use PHPUnit\Framework\TestCase;

class XMLWriterSitemapIndexRenderTest extends TestCase
{
    /**
     * @var XMLWriterSitemapIndexRender
     */
    private $render;

    protected function setUp(): void
    {
        $this->render = new XMLWriterSitemapIndexRender();
    }

    public function testStart(): void
    {
        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

        self::assertEquals($expected, $this->render->start());
    }

    public function testDoubleStart(): void
    {
        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

        self::assertEquals($expected, $this->render->start());
        self::assertEquals($expected, $this->render->start());
    }

    public function testEndNotStarted(): void
    {
        self::assertEquals('</sitemapindex>'.PHP_EOL, $this->render->end());
    }

    public function testStartEnd(): void
    {
        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL.
            '</sitemapindex>'.PHP_EOL
        ;

        self::assertEquals($expected, $this->render->start().$this->render->end());
    }

    public function testAddSitemapInNotStarted(): void
    {
        $path = 'https://example.com/sitemap1.xml';

        $expected =
            '<sitemap>'.
                '<loc>'.$path.'</loc>'.
            '</sitemap>'
        ;

        self::assertEquals($expected, $this->render->sitemap($path));
    }

    public function testAddSitemapInNotStartedUseIndent(): void
    {
        $render = new XMLWriterSitemapIndexRender(true);
        $path = 'https://example.com/sitemap1.xml';

        $expected =
            ' <sitemap>'.PHP_EOL.
            '  <loc>'.$path.'</loc>'.PHP_EOL.
            ' </sitemap>'.PHP_EOL
        ;

        self::assertEquals($expected, $render->sitemap($path));
    }

    public function testSitemap(): void
    {
        $path = 'https://example.com/sitemap1.xml';

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL.
                '<sitemap>'.
                    '<loc>'.$path.'</loc>'.
                '</sitemap>'.
            '</sitemapindex>'.PHP_EOL
        ;

        self::assertEquals($expected, $this->render->start().$this->render->sitemap($path).$this->render->end());
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
        $path = 'https://example.com/sitemap1.xml';

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL.
                '<sitemap>'.
                    '<loc>'.$path.'</loc>'.
                    '<lastmod>'.$last_modify->format('c').'</lastmod>'.
                '</sitemap>'.
            '</sitemapindex>'.PHP_EOL
        ;

        $actual = $this->render->start().$this->render->sitemap($path, $last_modify).$this->render->end();
        self::assertEquals($expected, $actual);
    }

    public function testSitemapUseIndent(): void
    {
        $render = new XMLWriterSitemapIndexRender(true);
        $path = 'https://example.com/sitemap1.xml';

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL.
            ' <sitemap>'.PHP_EOL.
            '  <loc>'.$path.'</loc>'.PHP_EOL.
            ' </sitemap>'.PHP_EOL.
            '</sitemapindex>'.PHP_EOL
        ;

        self::assertEquals($expected, $render->start().$render->sitemap($path).$render->end());
    }

    /**
     * @dataProvider getLastMod
     *
     * @param \DateTimeInterface $last_mod
     */
    public function testSitemapUseIndentWithLastMod(\DateTimeInterface $last_mod): void
    {
        $render = new XMLWriterSitemapIndexRender(true);
        $path = 'https://example.com/sitemap1.xml';

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL.
            ' <sitemap>'.PHP_EOL.
            '  <loc>'.$path.'</loc>'.PHP_EOL.
            '  <lastmod>'.$last_mod->format('c').'</lastmod>'.PHP_EOL.
            ' </sitemap>'.PHP_EOL.
            '</sitemapindex>'.PHP_EOL
        ;

        self::assertEquals($expected, $render->start().$render->sitemap($path, $last_mod).$render->end());
    }

    public function testStreamRender(): void
    {
        $path1 = 'https://foo.example.com/sitemap.xml';
        $path2 = 'https://bar.example.com/sitemap.xml';

        $actual = $this->render->start().$this->render->sitemap($path1);
        // render end string right after render first Sitemap and before another Sitemaps
        // this is necessary to calculate the size of the sitemap index in bytes
        $end = $this->render->end();
        $actual .= $this->render->sitemap($path2).$end;

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL.
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

    public function testStreamRenderUseIndent(): void
    {
        $render = new XMLWriterSitemapIndexRender(true);
        $path1 = 'https://foo.example.com/sitemap.xml';
        $path2 = 'https://bar.example.com/sitemap.xml';

        $actual = $render->start().$render->sitemap($path1);
        // render end string right after render first Sitemap and before another Sitemaps
        // this is necessary to calculate the size of the sitemap index in bytes
        $end = $render->end();
        $actual .= $render->sitemap($path2).$end;

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL.
            ' <sitemap>'.PHP_EOL.
            '  <loc>'.$path1.'</loc>'.PHP_EOL.
            ' </sitemap>'.PHP_EOL.
            ' <sitemap>'.PHP_EOL.
            '  <loc>'.$path2.'</loc>'.PHP_EOL.
            ' </sitemap>'.PHP_EOL.
            '</sitemapindex>'.PHP_EOL
        ;

        self::assertEquals($expected, $actual);
    }
}
