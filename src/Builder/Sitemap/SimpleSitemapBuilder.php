<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap;

use GpsLab\Component\Sitemap\Builder\Url\UrlBuilderCollection;
use GpsLab\Component\Sitemap\Url\Aggregator\UrlAggregator;

class SimpleSitemapBuilder
{
    /**
     * @var UrlBuilderCollection
     */
    private $builders;

    /**
     * @var UrlAggregator
     */
    private $aggregator;

    /**
     * @param UrlBuilderCollection $builders
     * @param UrlAggregator        $aggregator
     */
    public function __construct(UrlBuilderCollection $builders, UrlAggregator $aggregator)
    {
        $this->builders = $builders;
        $this->aggregator = $aggregator;
    }

    /**
     * @return int
     */
    public function build()
    {
        foreach ($this->builders as $i => $builder) {
            foreach ($builder as $url) {
                $this->aggregator->add($url);
            }
        }

        return count($this->aggregator);
    }
}
