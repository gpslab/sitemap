<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Sitemap;

use GpsLab\Component\Sitemap\Location;
use GpsLab\Component\Sitemap\Sitemap\Exception\InvalidLastModifyException;
use GpsLab\Component\Sitemap\Sitemap\Exception\InvalidLocationException;

/**
 * The part of sitemap index.
 */
class Sitemap
{
    /**
     * @var string
     */
    private $location;

    /**
     * @var \DateTimeInterface|null
     */
    private $last_modify;

    /**
     * @param string                  $location
     * @param \DateTimeInterface|null $last_modify
     */
    public function __construct(string $location, ?\DateTimeInterface $last_modify = null)
    {
        if (!Location::isValid($location)) {
            throw InvalidLocationException::invalid($location);
        }

        if ($last_modify instanceof \DateTimeInterface && $last_modify->getTimestamp() > time()) {
            throw InvalidLastModifyException::lookToFuture($last_modify);
        }

        $this->location = $location;
        $this->last_modify = $last_modify;
    }

    /**
     * @return string
     */
    public function getLocation(): string
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
