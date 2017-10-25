<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Aggregator;

use GpsLab\Component\Sitemap\Url\Aggregator\Exception\AggregationFinishedException;
use GpsLab\Component\Sitemap\Url\Url;
use Psr\Log\LoggerInterface;

class LoggerUrlAggregator
{
    /**
     * @var UrlAggregator
     */
    private $aggregator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Aggregation finished.
     *
     * @var bool
     */
    private $finished = false;

    /**
     * @param UrlAggregator   $aggregator
     * @param LoggerInterface $logger
     */
    public function __construct(UrlAggregator $aggregator, LoggerInterface $logger)
    {
        $this->aggregator = $aggregator;
        $this->logger = $logger;
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
        $this->logger->debug(sprintf('URL "%s" is added to sitemap', $url->getLoc()), [
            'changefreq' => $url->getChangeFreq(),
            'lastmod' => $url->getLastMod(),
            'priority' => $url->getPriority(),
        ]);
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

        $this->aggregator->finish();
        $this->finished = true;
    }

    public function __destruct()
    {
        if (!$this->finished) {
            $this->finish();
        }
    }
}
