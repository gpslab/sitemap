<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Builder;

use GpsLab\Component\Sitemap\Uri\Uri;

interface Builder extends \Countable, \Iterator
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return Uri
     */
    public function current();
}
