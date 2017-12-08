<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Builder\Sitemap;

use GpsLab\Component\Sitemap\Builder\Url\UrlBuilderCollection;
use GpsLab\Component\Sitemap\Stream\Stream;
use Symfony\Component\Console\Style\SymfonyStyle;

class SymfonySitemapBuilder
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
     * @param SymfonyStyle $io
     *
     * @return int
     */
    public function build(SymfonyStyle $io)
    {
        $total_builders = count($this->builders);
        $this->stream->open();

        foreach ($this->builders as $i => $builder) {
            $io->section(sprintf(
                '[%d/%d] Build by <info>%s</info> builder',
                $i + 1,
                $total_builders,
                $builder->getName()
            ));

            $io->progressStart(count($builder));
            foreach ($builder as $url) {
                $this->stream->push($url);
                $io->progressAdvance();
            }
            $io->progressFinish();
        }

        $total_urls = count($this->stream);
        $this->stream->close();

        return $total_urls;
    }
}
