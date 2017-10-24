<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Builder\Url;

class UrlBuilderCollection
{
    /**
     * @var UrlBuilder[]
     */
    private $builders = [];

    /**
     * @param UrlBuilder[] $builders
     */
    public function __construct(array $builders = [])
    {
        foreach ($builders as $builder) {
            $this->addBuilder($builder);
        }
    }

    /**
     * @param UrlBuilder $builder
     *
     * @return self
     */
    public function addBuilder(UrlBuilder $builder)
    {
        $this->builders[] = $builder;

        return $this;
    }

    /**
     * @return UrlBuilder[]
     */
    public function getBuilders()
    {
        return $this->builders;
    }
}
