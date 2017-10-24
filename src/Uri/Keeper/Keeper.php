<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Uri\Keeper;

use GpsLab\Component\Sitemap\Uri\Uri;

interface Keeper
{
    /**
     * @param Uri $url
     *
     * @return Keeper
     */
    public function addUri(Uri $url);

    /**
     * @return bool
     */
    public function save();
}
