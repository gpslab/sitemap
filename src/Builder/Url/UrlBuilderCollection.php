<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Builder\Url;

class UrlBuilderCollection implements \Countable, \IteratorAggregate
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
            $this->add($builder);
        }
    }

    /**
     * @param UrlBuilder $builder
     */
    public function add(UrlBuilder $builder)
    {
        $this->builders[] = $builder;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->builders);
    }

    /**
     * @return \Generator|UrlBuilder[]
     */
    public function getIterator()
    {
        foreach ($this->builders as $builder) {
            yield $builder;
        }
    }
}
