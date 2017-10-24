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
use GpsLab\Component\Sitemap\Result\Result;

class SimpleSitemapBuilder
{
    /**
     * @var UrlBuilderCollection
     */
    private $builders;

    /**
     * @var Result
     */
    private $result;

    /**
     * @param UrlBuilderCollection $builders
     * @param Result               $result
     */
    public function __construct(UrlBuilderCollection $builders, Result $result)
    {
        $this->builders = $builders;
        $this->result = $result;
    }

    /**
     * @return int
     */
    public function build()
    {
        foreach ($this->builders as $i => $builder) {
            foreach ($builder as $url) {
                $this->result->addUri($url);
            }
        }

        return $this->result->save();
    }
}
