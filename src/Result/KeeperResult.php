<?php
/**
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Component\Sitemap\Result;

use GpsLab\Component\Sitemap\Uri\Keeper\KeeperInterface;
use GpsLab\Component\Sitemap\Uri\UriInterface;

class KeeperResult implements ResultInterface
{
    /**
     * @var KeeperInterface
     */
    protected $keeper;

    /**
     * @var integer
     */
    protected $total = 0;

    /**
     * @param KeeperInterface $keeper
     */
    public function __construct(KeeperInterface $keeper)
    {
        $this->keeper = $keeper;
    }

    /**
     * @param UriInterface $url
     *
     * @return self
     */
    public function addUri(UriInterface $url)
    {
        $this->keeper->addUri($url);
        $this->total++;

        return $this;
    }

    /**
     * @return integer
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return bool
     */
    public function save()
    {
        $result = $this->keeper->save();
        $this->reset();

        return $result;
    }

    public function reset()
    {
        $this->total = 0;
        $this->keeper->reset();
    }
}
