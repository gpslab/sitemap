<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Component\Sitemap\Uri\Keeper;

use GpsLab\Component\Sitemap\Uri\UriInterface;

interface KeeperInterface
{
    /**
     * @param UriInterface $url
     *
     * @return KeeperInterface
     */
    public function addUri(UriInterface $url);

    /**
     * @return bool
     */
    public function save();
}
