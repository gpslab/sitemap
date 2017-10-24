<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Result;

use GpsLab\Component\Sitemap\Uri\Keeper\Keeper;
use GpsLab\Component\Sitemap\Uri\Url;

class KeeperResult implements Result
{
    /**
     * @var Keeper
     */
    private $keeper;

    /**
     * @var int
     */
    private $total = 0;

    const LINKS_LIMIT = 50000;

    /**
     * @param Keeper $keeper
     */
    public function __construct(Keeper $keeper)
    {
        $this->keeper = $keeper;
    }

    /**
     * @param Url $url
     *
     * @return self
     */
    public function addUri(Url $url)
    {
        if ($this->total < self::LINKS_LIMIT) {
            $this->keeper->addUri($url);
            ++$this->total;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function save()
    {
        $this->keeper->save();
        $total = $this->total;
        $this->total = 0;

        return $total;
    }
}
