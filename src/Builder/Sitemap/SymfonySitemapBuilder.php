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
use Symfony\Component\Console\Style\SymfonyStyle;

class SymfonySitemapBuilder
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
     * @param SymfonyStyle $io
     *
     * @return int
     */
    public function build(SymfonyStyle $io)
    {
        $total = count($this->builders);

        foreach ($this->builders as $i => $builder) {
            // show builder number
            $io->section(sprintf('[%d/%d] Build for <info>%s</info> builder', $i + 1, $total, $builder->getName()));

            $io->progressStart(count($builder));
            foreach ($builder as $url) {
                $this->aggregator->add($url);
                $io->progressAdvance();
            }
            $io->progressFinish();
        }

        return count($this->aggregator);
    }
}
