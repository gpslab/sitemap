<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Builder\Url;

use GpsLab\Component\Sitemap\Builder\Url\MultiUrlBuilder;
use GpsLab\Component\Sitemap\Builder\Url\UrlBuilder;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MultiUrlBuilderTest extends TestCase
{
    public function testIterate(): void
    {
        $urls = [];
        $builders = [
            $this->createUrlBuilder($urls, 'https://example.com/news', 3),
            $this->createUrlBuilder($urls, 'https://example.com/articles', 3),
        ];
        $builder = new MultiUrlBuilder($builders);

        $builder->add($this->createUrlBuilder($urls, 'https://example.com/posts', 3));

        foreach ($builder as $i => $url) {
            self::assertEquals($urls[$i], $url);
        }
    }

    /**
     * @param Url[]  $urls
     * @param string $location
     * @param int    $limit
     *
     * @return UrlBuilder&MockObject
     */
    private function createUrlBuilder(array &$urls, string $location, int $limit): UrlBuilder
    {
        $builder_urls = [];
        for ($i = 0; $i < $limit; ++$i) {
            $builder_urls[] = $urls[] = Url::create($location.'?page='.$i);
        }

        $builder = $this->createMock(UrlBuilder::class);
        $builder
            ->expects(self::once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($builder_urls))
        ;

        return $builder;
    }
}
