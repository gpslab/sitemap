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
use GpsLab\Component\Sitemap\Builder\Url\UrlBuilderCollection;

class UrlBuilderCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCollection()
    {
        $builders = [
            $this->getMock(UrlBuilder::class),
            $this->getMock(UrlBuilder::class),
            $this->getMock(UrlBuilder::class),
        ];
        $collection = new UrlBuilderCollection($builders);

        $this->assertEquals(count($builders), count($collection));

        foreach ($collection as $i => $builder) {
            $this->assertEquals($builders[$i], $builder);
        }

        /* @var $new_builder \PHPUnit_Framework_MockObject_MockObject|UrlBuilder */
        $new_builder = $this->getMock(UrlBuilder::class);
        $collection->add($new_builder);

        $collection = iterator_to_array($collection);
        $this->assertEquals($new_builder, array_pop($collection));
    }
}
