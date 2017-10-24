<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Builder;

use GpsLab\Component\Sitemap\Result\KeeperUri;
use Symfony\Component\Console\Style\SymfonyStyle;

interface Builder
{
    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param KeeperUri $result
     * @param SymfonyStyle $io
     */
    public function execute(KeeperUri $result, SymfonyStyle $io);
}
