<?php
/**
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Component\Sitemap\Result;

interface ResultInterface extends KeeperUriInterface
{
    /**
     * @return integer
     */
    public function getTotal();

    /**
     * @return bool
     */
    public function save();

    public function reset();
}
