<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Keeper;

use GpsLab\Component\Sitemap\Url\Url;

interface Keeper
{
    /**
     * @param Url $url
     *
     * @return Keeper
     */
    public function addUri(Url $url);

    /**
     * @return bool
     */
    public function save();
}
