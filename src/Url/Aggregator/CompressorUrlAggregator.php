<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Aggregator;

use GpsLab\Component\Compressor\CompressorInterface;
use GpsLab\Component\Sitemap\Url\Aggregator\Exception\AggregationFinishedException;
use GpsLab\Component\Sitemap\Url\Url;

class CompressorUrlAggregator
{
    /**
     * @var UrlAggregator
     */
    private $aggregator;

    /**
     * @var CompressorInterface
     */
    private $compressor;

    /**
     * @var string
     */
    private $filename = '';

    /**
     * Aggregation finished.
     *
     * @var bool
     */
    private $finished = false;

    /**
     * @param UrlAggregator       $aggregator
     * @param CompressorInterface $compressor
     * @param string              $filename
     */
    public function __construct(UrlAggregator $aggregator, CompressorInterface $compressor, $filename)
    {
        $this->aggregator = $aggregator;
        $this->compressor = $compressor;
        $this->filename = $filename;
    }

    /**
     * @param Url $url
     */
    public function add(Url $url)
    {
        if ($this->finished) {
            throw AggregationFinishedException::finished();
        }

        $this->aggregator->add($url);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->aggregator->count();
    }

    /**
     * Always finish URL aggregation.
     */
    public function finish()
    {
        if ($this->finished) {
            throw AggregationFinishedException::finished();
        }

        $this->compressor->compress($this->filename);
        $this->finished = true;
    }

    public function __destruct()
    {
        if (!$this->finished) {
            $this->finish();
        }
    }
}
