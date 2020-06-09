<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Render;

use GpsLab\Component\Sitemap\Render\PlainTextSitemapIndexRender;
use GpsLab\Component\Sitemap\Sitemap\Sitemap;
use PHPUnit\Framework\TestCase;

final class PlainTextSitemapIndexRenderTest extends TestCase
{
    /**
     * @var string
     */
    private const WEB_PATH = 'https://example.com';

    /**
     * @var PlainTextSitemapIndexRender
     */
    private $render;

    protected function setUp(): void
    {
        $this->render = new PlainTextSitemapIndexRender(self::WEB_PATH);
    }

    /**
     * @return array<int, array<int, string|bool>>
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
        $render = new PlainTextSitemapIndexRender(self::WEB_PATH, $validating);
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
        $path = '/sitemap1.xml';

        $expected = '<sitemap>'.
            '<loc>'.self::WEB_PATH.$path.'</loc>'.
        '</sitemap>';

        self::assertEquals($expected, $this->render->sitemap(new Sitemap($path)));
    }

    /**
     * @return array<int, array<int, \DateTimeInterface|null>>
     */
    public function getLastMod(): array
    {
        return [
            [null],
            [new \DateTime('-1 day')],
            [new \DateTimeImmutable('-1 day')],
        ];
    }

    /**
     * @dataProvider getLastMod
     *
     * @param \DateTimeInterface|null $last_modify
     */
    public function testSitemapWithLastMod(?\DateTimeInterface $last_modify): void
    {
        $path = '/sitemap1.xml';

        $expected = '<sitemap>'.
            '<loc>'.self::WEB_PATH.$path.'</loc>'.
            ($last_modify ? sprintf('<lastmod>%s</lastmod>', $last_modify->format('c')) : '').
        '</sitemap>';

        self::assertEquals($expected, $this->render->sitemap(new Sitemap($path, $last_modify)));
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testStreamRender(bool $validating, string $start_teg): void
    {
        $render = new PlainTextSitemapIndexRender(self::WEB_PATH, $validating);
        $path1 = '/sitemap1.xml';
        $path2 = '/sitemap1.xml';

        $actual = $render->start().$render->sitemap(new Sitemap($path1));
        // render end string right after render first Sitemap and before another Sitemaps
        // this is necessary to calculate the size of the sitemap index in bytes
        $end = $render->end();
        $actual .= $render->sitemap(new Sitemap($path2)).$end;

        $expected = '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.
            $start_teg.
                '<sitemap>'.
                    '<loc>'.self::WEB_PATH.$path1.'</loc>'.
                '</sitemap>'.
                '<sitemap>'.
                    '<loc>'.self::WEB_PATH.$path2.'</loc>'.
                '</sitemap>'.
            '</sitemapindex>'.PHP_EOL
        ;

        self::assertEquals($expected, $actual);
    }
}
