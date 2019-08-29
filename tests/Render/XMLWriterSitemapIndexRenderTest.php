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
        $render = new XMLWriterSitemapIndexRender($validating);
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
        $render = new XMLWriterSitemapIndexRender($validating);
        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.$start_teg.PHP_EOL;

        self::assertEquals($expected, $render->start());
        self::assertEquals($expected, $render->start());
    }

    public function testEndNotStarted(): void
    {
        self::assertEquals('</sitemapindex>'.PHP_EOL, $this->render->end());
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testStartEnd(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapIndexRender($validating);
        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
            '</sitemapindex>'.PHP_EOL
        ;

        self::assertEquals($expected, $render->start().$render->end());
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
        $render = new XMLWriterSitemapIndexRender(false, true);
        $path = 'https://example.com/sitemap1.xml';

        $expected =
            ' <sitemap>'.PHP_EOL.
            '  <loc>'.$path.'</loc>'.PHP_EOL.
            ' </sitemap>'.PHP_EOL
        ;

        self::assertEquals($expected, $render->sitemap($path));
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testSitemap(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapIndexRender($validating);
        $path = 'https://example.com/sitemap1.xml';

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
                '<sitemap>'.
                    '<loc>'.$path.'</loc>'.
                '</sitemap>'.
            '</sitemapindex>'.PHP_EOL
        ;

        self::assertEquals($expected, $render->start().$render->sitemap($path).$render->end());
    }

    /**
     * @return array
     */
    public function getLastModify(): array
    {
        $result = [];
        foreach ($this->getValidating() as $params) {
            $result[] = array_merge([new \DateTime('-1 day')], $params);
        }
        foreach ($this->getValidating() as $params) {
            $result[] = array_merge([new \DateTimeImmutable('-1 day')], $params);
        }

        return $result;
    }

    /**
     * @dataProvider getLastModify
     *
     * @param \DateTimeInterface $last_modify
     * @param bool               $validating
     * @param string             $start_teg
     */
    public function testSitemapWithLastModify(
        \DateTimeInterface $last_modify,
        bool $validating,
        string $start_teg
    ): void {
        $render = new XMLWriterSitemapIndexRender($validating);
        $path = 'https://example.com/sitemap1.xml';

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
                '<sitemap>'.
                    '<loc>'.$path.'</loc>'.
                    '<lastmod>'.$last_modify->format('c').'</lastmod>'.
                '</sitemap>'.
            '</sitemapindex>'.PHP_EOL
        ;

        $actual = $render->start().$render->sitemap($path, $last_modify).$render->end();
        self::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testSitemapUseIndent(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapIndexRender($validating, true);
        $path = 'https://example.com/sitemap1.xml';

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
            ' <sitemap>'.PHP_EOL.
            '  <loc>'.$path.'</loc>'.PHP_EOL.
            ' </sitemap>'.PHP_EOL.
            '</sitemapindex>'.PHP_EOL
        ;

        self::assertEquals($expected, $render->start().$render->sitemap($path).$render->end());
    }

    /**
     * @dataProvider getLastModify
     *
     * @param \DateTimeInterface $last_modify
     * @param bool               $validating
     * @param string             $start_teg
     */
    public function testSitemapUseIndentWithLastModify(
        \DateTimeInterface $last_modify,
        bool $validating,
        string $start_teg
    ): void {
        $render = new XMLWriterSitemapIndexRender($validating, true);
        $path = 'https://example.com/sitemap1.xml';

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
            ' <sitemap>'.PHP_EOL.
            '  <loc>'.$path.'</loc>'.PHP_EOL.
            '  <lastmod>'.$last_modify->format('c').'</lastmod>'.PHP_EOL.
            ' </sitemap>'.PHP_EOL.
            '</sitemapindex>'.PHP_EOL
        ;

        self::assertEquals($expected, $render->start().$render->sitemap($path, $last_modify).$render->end());
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testStreamRender(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapIndexRender($validating);
        $path1 = 'https://example.com/sitemap1.xml';
        $path2 = 'https://example.com/sitemap1.xml';

        $actual = $render->start().$render->sitemap($path1);
        // render end string right after render first Sitemap and before another Sitemaps
        // this is necessary to calculate the size of the sitemap index in bytes
        $end = $render->end();
        $actual .= $render->sitemap($path2).$end;

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
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

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testStreamRenderUseIndent(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapIndexRender($validating, true);
        $path1 = 'https://example.com/sitemap1.xml';
        $path2 = 'https://example.com/sitemap1.xml';

        $actual = $render->start().$render->sitemap($path1);
        // render end string right after render first Sitemap and before another Sitemaps
        // this is necessary to calculate the size of the sitemap index in bytes
        $end = $render->end();
        $actual .= $render->sitemap($path2).$end;

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.
            $start_teg.PHP_EOL.
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
