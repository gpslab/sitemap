<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Unit\Builder\Sitemap;

use GpsLab\Component\Sitemap\Builder\Sitemap\SilentSitemapBuilder;
use GpsLab\Component\Sitemap\Builder\Url\UrlBuilder;
use GpsLab\Component\Sitemap\Builder\Url\UrlBuilderCollection;
use GpsLab\Component\Sitemap\Stream\Stream;
use GpsLab\Component\Sitemap\Url\Url;

class SilentSitemapBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UrlBuilderCollection
     */
    private $collection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Stream
     */
    private $stream;

    /**
     * @var SilentSitemapBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->collection = $this->getMock(UrlBuilderCollection::class);
        $this->stream = $this->getMock(Stream::class);

        $this->builder = new SilentSitemapBuilder($this->collection, $this->stream);
    }

    public function testBuild()
    {
        $urls = [
            $this->getMockUrl(),
            $this->getMockUrl(),
            $this->getMockUrl(),
            $this->getMockUrl(),
            $this->getMockUrl(),
        ];

        /* @var $builders \PHPUnit_Framework_MockObject_MockObject[]|UrlBuilder[] */
        $builders = [
            $this->getMock(UrlBuilder::class),
            $this->getMock(UrlBuilder::class),
        ];
        foreach ($builders as $i => $builder) {
            if ($i) {
                $slice = floor(count($urls) / count($builders));
            } else {
                $slice = ceil(count($urls) / count($builders));
            }

            $builder
                ->expects($this->once())
                ->method('getIterator')
                ->will($this->returnValue(new \ArrayIterator(array_slice($urls, $slice * $i, $slice))))
            ;
        }

        $this->collection
            ->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator($builders)))
        ;

        $this->stream
            ->expects($this->once())
            ->method('open')
        ;
        $this->stream
            ->expects($this->once())
            ->method('close')
        ;
        $this->stream
            ->expects($this->once())
            ->method('count')
            ->will($this->returnValue(count($urls)))
        ;
        foreach ($urls as $i => $url) {
            $this->stream
                ->expects($this->at($i + 1))
                ->method('push')
                ->with($url)
            ;
        }

        $this->assertEquals(count($urls), $this->builder->build());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Url
     */
    private function getMockUrl()
    {
        return $this
            ->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
}
