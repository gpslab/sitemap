<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Result;

use GpsLab\Component\Sitemap\Uri\Uri;

interface KeeperUri
{
    /**
     * @param Uri $url
     *
     * @return self
     */
    public function addUri(Uri $url);
}
