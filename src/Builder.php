<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap;

use GpsLab\Component\Sitemap\Builder\BuilderCollection;
use GpsLab\Component\Sitemap\Result\Result;
use Symfony\Component\Console\Style\SymfonyStyle;

class Builder
{
    /**
     * @var BuilderCollection
     */
    private $builders;

    /**
     * @var Result
     */
    private $result;

    /**
     * @param BuilderCollection $builders
     * @param Result $result
     */
    public function __construct(BuilderCollection $builders, Result $result)
    {
        $this->builders = $builders;
        $this->result = $result;
    }

    /**
     * @param SymfonyStyle $io
     *
     * @return int
     */
    public function build(SymfonyStyle $io)
    {
        $builders = $this->builders->getBuilders();
        $total = count($builders);

        for ($i = 1; $i <= $total; ++$i) {
            // show builder number
            $io->section(sprintf('[%d/%d] Build for <info>%s</info> builder', $i, $total, $builders[$i]->getTitle()));

            $builders[$i]->execute($this->result, $io);
        }

        return $this->result->save();
    }
}
