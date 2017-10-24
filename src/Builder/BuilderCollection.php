<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Builder;

class BuilderCollection
{
    /**
     * @var Builder[]
     */
    private $builders = [];

    /**
     * @param Builder[] $builders
     */
    public function __construct(array $builders = [])
    {
        foreach ($builders as $builder) {
            $this->addBuilder($builder);
        }
    }

    /**
     * @param Builder $builder
     *
     * @return self
     */
    public function addBuilder(Builder $builder)
    {
        $this->builders[] = $builder;

        return $this;
    }

    /**
     * @return Builder[]
     */
    public function getBuilders()
    {
        return $this->builders;
    }
}
