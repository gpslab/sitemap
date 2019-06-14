<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Builder\Sitemap;

use GpsLab\Component\Sitemap\Builder\Sitemap\SymfonySitemapBuilder;
use GpsLab\Component\Sitemap\Builder\Url\UrlBuilder;
use GpsLab\Component\Sitemap\Builder\Url\UrlBuilderCollection;
use GpsLab\Component\Sitemap\Stream\Stream;
use GpsLab\Component\Sitemap\Url\Url;
use Symfony\Component\Console\Style\SymfonyStyle;

class SymfonySitemapBuilderTest extends \PHPUnit_Framework_TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject|SymfonyStyle
     */
    private $style;

    /**
     * @var SymfonySitemapBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->collection = $this->getMock(UrlBuilderCollection::class);
        $this->stream = $this->getMock(Stream::class);
        $this->style = $this
            ->getMockBuilder(SymfonyStyle::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->builder = new SymfonySitemapBuilder($this->collection, $this->stream);
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
        $style_index = 0;
        foreach ($builders as $i => $builder) {
            if ($i) {
                $slice = floor(count($urls) / count($builders));
            } else {
                $slice = ceil(count($urls) / count($builders));
            }

            $name = 'builder'.$i;

            $builder
                ->expects($this->once())
                ->method('getIterator')
                ->will($this->returnValue(new \ArrayIterator(array_slice($urls, $slice * $i, $slice))))
            ;
            $builder
                ->expects($this->once())
                ->method('getName')
                ->will($this->returnValue($name))
            ;
            $builder
                ->expects($this->once())
                ->method('count')
                ->will($this->returnValue($slice))
            ;

            $this->style
                ->expects($this->at($style_index++))
                ->method('section')
                ->with(sprintf('[%d/%d] Build by <info>%s</info> builder', $i + 1, count($builders), $name))
            ;
            $this->style
                ->expects($this->at($style_index++))
                ->method('progressStart')
                ->with($slice)
            ;
            for ($i = 0; $i < $slice; ++$i) {
                $this->style
                    ->expects($this->at($style_index++))
                    ->method('progressAdvance')
                ;
            }
            $this->style
                ->expects($this->at($style_index++))
                ->method('progressFinish')
            ;
        }

        $this->collection
            ->expects($this->atLeastOnce())
            ->method('count')
            ->will($this->returnValue(count($builders)))
        ;
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

        $this->assertEquals(count($urls), $this->builder->build($this->style));
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
