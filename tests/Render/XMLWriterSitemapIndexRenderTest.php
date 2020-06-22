<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Render;

use GpsLab\Component\Sitemap\Render\XMLWriterSitemapIndexRender;
use GpsLab\Component\Sitemap\Sitemap\Sitemap;
use PHPUnit\Framework\TestCase;

final class XMLWriterSitemapIndexRenderTest extends TestCase
{
    /**
     * XMLWriter always use LF as end of line character and on Windows too.
     */
    private const EOL = "\n";

    private const WEB_PATH = 'https://example.com';

    /**
     * @var XMLWriterSitemapIndexRender
     */
    private $render;

    protected function setUp(): void
    {
        $this->render = new XMLWriterSitemapIndexRender(self::WEB_PATH);
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
        $render = new XMLWriterSitemapIndexRender(self::WEB_PATH, $validating);
        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL.$start_teg.self::EOL;

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
        $render = new XMLWriterSitemapIndexRender(self::WEB_PATH, $validating);
        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL.$start_teg.self::EOL;

        self::assertEquals($expected, $render->start());
        self::assertEquals($expected, $render->start());
    }

    public function testEndNotStarted(): void
    {
        self::assertEquals('</sitemapindex>'.self::EOL, $this->render->end());
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testStartEnd(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapIndexRender(self::WEB_PATH, $validating);
        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL.
            $start_teg.self::EOL.
            '</sitemapindex>'.self::EOL
        ;

        self::assertEquals($expected, $render->start().$render->end());
    }

    public function testAddSitemapInNotStarted(): void
    {
        $path = '/sitemap1.xml';

        $expected =
            '<sitemap>'.
                '<loc>'.self::WEB_PATH.$path.'</loc>'.
            '</sitemap>'
        ;

        self::assertEquals($expected, $this->render->sitemap(new Sitemap($path)));
    }

    public function testAddSitemapInNotStartedUseIndent(): void
    {
        $render = new XMLWriterSitemapIndexRender(self::WEB_PATH, false, true);
        $path = '/sitemap1.xml';

        $expected =
            ' <sitemap>'.self::EOL.
            '  <loc>'.self::WEB_PATH.$path.'</loc>'.self::EOL.
            ' </sitemap>'.self::EOL
        ;

        self::assertEquals($expected, $render->sitemap(new Sitemap($path)));
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testSitemap(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapIndexRender(self::WEB_PATH, $validating);
        $path = '/sitemap1.xml';

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL.
            $start_teg.self::EOL.
                '<sitemap>'.
                    '<loc>'.self::WEB_PATH.$path.'</loc>'.
                '</sitemap>'.
            '</sitemapindex>'.self::EOL
        ;

        self::assertEquals($expected, $render->start().$render->sitemap(new Sitemap($path)).$render->end());
    }

    /**
     * @return mixed[][]
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
        $render = new XMLWriterSitemapIndexRender(self::WEB_PATH, $validating);
        $path = '/sitemap1.xml';

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL.
            $start_teg.self::EOL.
                '<sitemap>'.
                    '<loc>'.self::WEB_PATH.$path.'</loc>'.
                    '<lastmod>'.$last_modify->format('c').'</lastmod>'.
                '</sitemap>'.
            '</sitemapindex>'.self::EOL
        ;

        $actual = $render->start().$render->sitemap(new Sitemap($path, $last_modify)).$render->end();
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
        $render = new XMLWriterSitemapIndexRender(self::WEB_PATH, $validating, true);
        $path = '/sitemap1.xml';

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL.
            $start_teg.self::EOL.
            ' <sitemap>'.self::EOL.
            '  <loc>'.self::WEB_PATH.$path.'</loc>'.self::EOL.
            ' </sitemap>'.self::EOL.
            '</sitemapindex>'.self::EOL
        ;

        self::assertEquals($expected, $render->start().$render->sitemap(new Sitemap($path)).$render->end());
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
        $render = new XMLWriterSitemapIndexRender(self::WEB_PATH, $validating, true);
        $path = '/sitemap1.xml';

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL.
            $start_teg.self::EOL.
            ' <sitemap>'.self::EOL.
            '  <loc>'.self::WEB_PATH.$path.'</loc>'.self::EOL.
            '  <lastmod>'.$last_modify->format('c').'</lastmod>'.self::EOL.
            ' </sitemap>'.self::EOL.
            '</sitemapindex>'.self::EOL
        ;

        $actual = $render->start().$render->sitemap(new Sitemap($path, $last_modify)).$render->end();

        self::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testStreamRender(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapIndexRender(self::WEB_PATH, $validating);
        $path1 = '/sitemap1.xml';
        // test escaping
        $path2 = '/sitemap1.xml?foo=\'bar\'&baz=">"&zaz=<';

        $actual = $render->start().$render->sitemap(new Sitemap($path1));
        // render end string right after render first Sitemap and before another Sitemaps
        // this is necessary to calculate the size of the sitemap index in bytes
        $end = $render->end();
        $actual .= $render->sitemap(new Sitemap($path2)).$end;

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL.
            $start_teg.self::EOL.
                '<sitemap>'.
                    '<loc>'.self::WEB_PATH.$path1.'</loc>'.
                '</sitemap>'.
                '<sitemap>'.
                    '<loc>'.htmlspecialchars(self::WEB_PATH.$path2).'</loc>'.
                '</sitemap>'.
            '</sitemapindex>'.self::EOL
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
        $render = new XMLWriterSitemapIndexRender(self::WEB_PATH, $validating, true);
        $path1 = '/sitemap1.xml';
        $path2 = '/sitemap1.xml';

        $actual = $render->start().$render->sitemap(new Sitemap($path1));
        // render end string right after render first Sitemap and before another Sitemaps
        // this is necessary to calculate the size of the sitemap index in bytes
        $end = $render->end();
        $actual .= $render->sitemap(new Sitemap($path2)).$end;

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL.
            $start_teg.self::EOL.
            ' <sitemap>'.self::EOL.
            '  <loc>'.self::WEB_PATH.$path1.'</loc>'.self::EOL.
            ' </sitemap>'.self::EOL.
            ' <sitemap>'.self::EOL.
            '  <loc>'.self::WEB_PATH.$path2.'</loc>'.self::EOL.
            ' </sitemap>'.self::EOL.
            '</sitemapindex>'.self::EOL
        ;

        self::assertEquals($expected, $actual);
    }
}
