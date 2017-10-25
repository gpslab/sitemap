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
use GpsLab\Component\Sitemap\Stream\Stream;

class SilentSitemapBuilder
{
    /**
     * @var UrlBuilderCollection
     */
    private $builders;

    /**
     * @var Stream
     */
    private $stream;

    /**
     * @param UrlBuilderCollection $builders
     * @param Stream               $stream
     */
    public function __construct(UrlBuilderCollection $builders, Stream $stream)
    {
        $this->builders = $builders;
        $this->stream = $stream;
    }

    /**
     * @return int
     */
    public function build()
    {
        $this->stream->open();

        foreach ($this->builders as $builder) {
            foreach ($builder as $url) {
                $this->stream->push($url);
            }
        }

        $total_urls = count($this->stream);
        $this->stream->close();

        return $total_urls;
    }
}
