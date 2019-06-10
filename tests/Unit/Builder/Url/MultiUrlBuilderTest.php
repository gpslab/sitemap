<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Unit\Builder\Url;

use GpsLab\Component\Sitemap\Builder\Url\UrlBuilder;
use GpsLab\Component\Sitemap\Builder\Url\MultiUrlBuilder;
use GpsLab\Component\Sitemap\Url\Url;

class MultiUrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testIterate()
    {
        $urls = [];
        $builders = [
            $this->createUrlBuilder($urls, 3),
            $this->createUrlBuilder($urls, 3),
            $this->createUrlBuilder($urls, 3),
        ];
        $builder = new MultiUrlBuilder($builders);

        $builder->add($this->createUrlBuilder($urls, 3));

        foreach ($builder as $i => $url) {
            $this->assertEquals($urls[$i], $url);
        }
    }

    /**
     * @param array $urls
     * @param int   $limit
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|UrlBuilder
     */
    private function createUrlBuilder(array &$urls, int $limit): UrlBuilder
    {
        $builder_urls = [];
        for ($i = 0; $i < $limit; $i++) {
            $builder_urls[] = $urls[] = $this
                ->getMockBuilder(Url::class)
                ->disableOriginalConstructor()
                ->getMock()
            ;
        }

        return new TestUrlBuilder($builder_urls);
    }
}
