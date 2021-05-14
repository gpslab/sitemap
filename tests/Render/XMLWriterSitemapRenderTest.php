<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Render;

use GpsLab\Component\Sitemap\Render\XMLWriterSitemapRender;
use GpsLab\Component\Sitemap\Url\ChangeFrequency;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\TestCase;

final class XMLWriterSitemapRenderTest extends TestCase
{
    /**
     * XMLWriter always use LF as end of line character and on Windows too.
     */
    private const EOL = "\n";

    /**
     * @var XMLWriterSitemapRender
     */
    private $render;

    protected function setUp(): void
    {
        $this->render = new XMLWriterSitemapRender();
    }

    /**
     * @return array<int, array<int, string|bool>>
     */
    public function getValidating(): array
    {
        return [
            [
                false,
                '<urlset'.
                ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.
                ' xmlns:xhtml="http://www.w3.org/1999/xhtml"'.
                '>',
            ],
            [
                true,
                '<urlset'.
                ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'.
                ' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9'.
                ' http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"'.
                ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.
                ' xmlns:xhtml="http://www.w3.org/1999/xhtml"'.
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
        $render = new XMLWriterSitemapRender($validating);
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
        $render = new XMLWriterSitemapRender($validating);
        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL.$start_teg.self::EOL;

        self::assertEquals($expected, $render->start());
        self::assertEquals($expected, $render->start());
    }

    public function testEndNotStarted(): void
    {
        self::assertEquals('</urlset>'.self::EOL, $this->render->end());
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testStartEnd(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapRender($validating);
        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL.
            $start_teg.self::EOL.
            '</urlset>'.self::EOL
        ;

        self::assertEquals($expected, $render->start().$render->end());
    }

    /**
     * @return Url[][]
     */
    public function getUrls(): array
    {
        return [
            [Url::create('https://example.com/')],
            [Url::create('https://example.com/', new \DateTimeImmutable('-1 day'))],
            [Url::create('https://example.com/', null, ChangeFrequency::WEEKLY)],
            [Url::create('https://example.com/', null, null, 10)],
            [Url::create('https://example.com/', null, ChangeFrequency::WEEKLY, 10)],
            [Url::create('https://example.com/', new \DateTimeImmutable('-1 day'), null, 10)],
            [Url::create('https://example.com/', new \DateTimeImmutable('-1 day'), ChangeFrequency::WEEKLY, null)],
            [Url::create('https://example.com/', new \DateTimeImmutable('-1 day'), ChangeFrequency::WEEKLY, 10)],
            [Url::create('https://example.com/?foo=\'bar\'&baz=">"&zaz=<')], // test escaping
            [Url::create(
                'https://example.com/english/page.html',
                new \DateTimeImmutable('-1 day'),
                ChangeFrequency::WEEKLY,
                10,
                [
                    'de' => 'https://de.example.com/page.html',
                    'de-ch' => 'https://example.com/schweiz-deutsch/page.html',
                    'en' => 'https://example.com/english/page.html',
                ]),
            ],
        ];
    }

    /**
     * @dataProvider getUrls
     *
     * @param Url $url
     */
    public function testAddUrlInNotStarted(Url $url): void
    {
        $expected = '<url>';
        $expected .= '<loc>'.htmlspecialchars((string) $url->getLocation()).'</loc>';

        if ($url->getLastModify()) {
            $expected .= '<lastmod>'.$url->getLastModify()->format('c').'</lastmod>';
        }

        if ($url->getChangeFrequency()) {
            $expected .= '<changefreq>'.$url->getChangeFrequency().'</changefreq>';
        }

        if ($url->getPriority()) {
            $expected .= '<priority>'.$url->getPriority().'</priority>';
        }

        foreach ($url->getLanguages() as $language) {
            $location = htmlspecialchars((string) $language->getLocation());
            $expected .= '<xhtml:link rel="alternate" hreflang="'.$language->getLanguage().'" href="'.$location.'"/>';
        }

        $expected .= '</url>';

        self::assertEquals($expected, $this->render->url($url));
    }

    /**
     * @dataProvider getUrls
     *
     * @param Url $url
     */
    public function testAddUrlInNotStartedUseIndent(Url $url): void
    {
        $render = new XMLWriterSitemapRender(false, true);

        $expected = ' <url>'.self::EOL;
        $expected .= '  <loc>'.htmlspecialchars((string) $url->getLocation()).'</loc>'.self::EOL;

        if ($url->getLastModify()) {
            $expected .= '  <lastmod>'.$url->getLastModify()->format('c').'</lastmod>'.self::EOL;
        }

        if ($url->getChangeFrequency()) {
            $expected .= '  <changefreq>'.$url->getChangeFrequency().'</changefreq>'.self::EOL;
        }

        if ($url->getPriority()) {
            $expected .= '  <priority>'.$url->getPriority().'</priority>'.self::EOL;
        }

        foreach ($url->getLanguages() as $language) {
            $location = htmlspecialchars((string) $language->getLocation());
            $expected .= '  <xhtml:link rel="alternate" hreflang="'.$language->getLanguage().'" href="'.$location.'"/>'.self::EOL;
        }

        $expected .= ' </url>'.self::EOL;

        self::assertEquals($expected, $render->url($url));
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testUrl(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapRender($validating);
        $url = Url::create(
            'https://example.com/',
            new \DateTimeImmutable('-1 day'),
            ChangeFrequency::WEEKLY,
            10
        );

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL.
            $start_teg.self::EOL.
                '<url>'.
                    '<loc>'.htmlspecialchars((string) $url->getLocation()).'</loc>'.
                    '<lastmod>'.$url->getLastModify()->format('c').'</lastmod>'.
                    '<changefreq>'.$url->getChangeFrequency().'</changefreq>'.
                    '<priority>'.$url->getPriority().'</priority>'.
                '</url>'.
            '</urlset>'.self::EOL
        ;

        self::assertEquals($expected, $render->start().$render->url($url).$render->end());
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testUrlUseIndent(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapRender($validating, true);
        $url = Url::create(
            'https://example.com/',
            new \DateTimeImmutable('-1 day'),
            ChangeFrequency::WEEKLY,
            10
        );

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL.
            $start_teg.self::EOL.
            ' <url>'.self::EOL.
            '  <loc>'.htmlspecialchars((string) $url->getLocation()).'</loc>'.self::EOL.
            '  <lastmod>'.$url->getLastModify()->format('c').'</lastmod>'.self::EOL.
            '  <changefreq>'.$url->getChangeFrequency().'</changefreq>'.self::EOL.
            '  <priority>'.$url->getPriority().'</priority>'.self::EOL.
            ' </url>'.self::EOL.
            '</urlset>'.self::EOL
        ;

        self::assertEquals($expected, $render->start().$render->url($url).$render->end());
    }

    /**
     * @dataProvider getValidating
     *
     * @param bool   $validating
     * @param string $start_teg
     */
    public function testStreamRender(bool $validating, string $start_teg): void
    {
        $render = new XMLWriterSitemapRender($validating);
        $url1 = Url::create(
            'https://example.com/',
            new \DateTimeImmutable('-1 day'),
            ChangeFrequency::WEEKLY,
            10
        );
        $url2 = Url::create(
            'https://example.com/about',
            new \DateTimeImmutable('-1 month'),
            ChangeFrequency::YEARLY,
            9
        );

        $actual = $render->start().$render->url($url1);
        // render end string right after render first URL and before another URLs
        // this is necessary to calculate the size of the sitemap in bytes
        $end = $render->end();
        $actual .= $render->url($url2).$end;

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL.
            $start_teg.self::EOL.
                '<url>'.
                    '<loc>'.htmlspecialchars((string) $url1->getLocation()).'</loc>'.
                    '<lastmod>'.$url1->getLastModify()->format('c').'</lastmod>'.
                    '<changefreq>'.$url1->getChangeFrequency().'</changefreq>'.
                    '<priority>'.$url1->getPriority().'</priority>'.
                '</url>'.
                '<url>'.
                    '<loc>'.htmlspecialchars((string) $url2->getLocation()).'</loc>'.
                    '<lastmod>'.$url2->getLastModify()->format('c').'</lastmod>'.
                    '<changefreq>'.$url2->getChangeFrequency().'</changefreq>'.
                    '<priority>'.$url2->getPriority().'</priority>'.
                '</url>'.
            '</urlset>'.self::EOL
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
        $render = new XMLWriterSitemapRender($validating, true);
        $url1 = Url::create(
            'https://example.com/',
            new \DateTimeImmutable('-1 day'),
            ChangeFrequency::WEEKLY,
            10
        );
        $url2 = Url::create(
            'https://example.com/about',
            new \DateTimeImmutable('-1 month'),
            ChangeFrequency::YEARLY,
            9
        );

        $actual = $render->start().$render->url($url1);
        // render end string right after render first URL and before another URLs
        // this is necessary to calculate the size of the sitemap in bytes
        $end = $render->end();
        $actual .= $render->url($url2).$end;

        $expected = '<?xml version="1.0" encoding="UTF-8"?>'.self::EOL.
            $start_teg.self::EOL.
            ' <url>'.self::EOL.
            '  <loc>'.htmlspecialchars((string) $url1->getLocation()).'</loc>'.self::EOL.
            '  <lastmod>'.$url1->getLastModify()->format('c').'</lastmod>'.self::EOL.
            '  <changefreq>'.$url1->getChangeFrequency().'</changefreq>'.self::EOL.
            '  <priority>'.$url1->getPriority().'</priority>'.self::EOL.
            ' </url>'.self::EOL.
            ' <url>'.self::EOL.
            '  <loc>'.htmlspecialchars((string) $url2->getLocation()).'</loc>'.self::EOL.
            '  <lastmod>'.$url2->getLastModify()->format('c').'</lastmod>'.self::EOL.
            '  <changefreq>'.$url2->getChangeFrequency().'</changefreq>'.self::EOL.
            '  <priority>'.$url2->getPriority().'</priority>'.self::EOL.
            ' </url>'.self::EOL.
            '</urlset>'.self::EOL
        ;

        self::assertEquals($expected, $actual);
    }
}
