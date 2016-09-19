<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Component\Sitemap\Builder;

class CollectionBuilder
{
    /**
     * @var BuilderInterface[]
     */
    private $builders = [];

    /**
     * @param BuilderInterface $builder
     *
     * @return self
     */
    public function addBuilder(BuilderInterface $builder)
    {
        $this->builders[] = $builder;

        return $this;
    }

    /**
     * @return BuilderInterface[]
     */
    public function getBuilders()
    {
        return $this->builders;
    }
}
