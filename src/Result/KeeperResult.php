<?php
/**
 * GpsLab component.
 *
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
     * @var int
     */
    protected $total = 0;

    const LINKS_LIMIT = 50000;

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
