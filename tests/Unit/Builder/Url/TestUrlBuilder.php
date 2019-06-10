<?php
declare(strict_types=1);

/**
 * Lupin package.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 */

namespace GpsLab\Component\Sitemap\Tests\Unit\Builder\Url;

use GpsLab\Component\Sitemap\Builder\Url\UrlBuilder;
use GpsLab\Component\Sitemap\Url\Url;

class TestUrlBuilder implements UrlBuilder
{
    /**
     * @var Url[]
     */
    private $urls = [];

    /**
     * @param Url[] $urls
     */
    public function __construct(array $urls)
    {
        $this->urls = $urls;
    }

    /**
     * @return Url[]|\Traversable
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->urls);
    }
}
