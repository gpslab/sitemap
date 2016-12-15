<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Builder;

use GpsLab\Component\Sitemap\Result\KeeperUriInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

interface BuilderInterface
{
    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param KeeperUriInterface $result
     * @param SymfonyStyle $io
     */
    public function execute(KeeperUriInterface $result, SymfonyStyle $io);
}
