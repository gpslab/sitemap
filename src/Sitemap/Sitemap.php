<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Sitemap;

use GpsLab\Component\Sitemap\Location;
use GpsLab\Component\Sitemap\Sitemap\Exception\InvalidLastModifyException;

/**
 * The part of sitemap index.
 */
class Sitemap
{
    /**
     * @var Location
     */
    private $location;

    /**
     * @var \DateTimeInterface|null
     */
    private $last_modify;

    /**
     * @param Location|string         $location
     * @param \DateTimeInterface|null $last_modify
     *
     * @throws InvalidLastModifyException
     */
    public function __construct($location, ?\DateTimeInterface $last_modify = null)
    {
        if ($last_modify instanceof \DateTimeInterface && $last_modify->getTimestamp() > time()) {
            throw InvalidLastModifyException::lookToFuture($last_modify);
        }

        $this->location = $location instanceof Location ? $location : new Location($location);
        $this->last_modify = $last_modify;
    }

    /**
     * @return Location
     */
    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getLastModify(): ?\DateTimeInterface
    {
        return $this->last_modify;
    }
}
