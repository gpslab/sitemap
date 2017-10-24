<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Uri;

class SimpleUri implements Uri
{
    /**
     * @var string
     */
    private $loc = '';

    /**
     * @var \DateTime
     */
    private $last_mod;

    /**
     * @var string
     */
    private $change_freq = self::DEFAULT_CHANGE_FREQ;

    /**
     * @var string
     */
    private $priority = self::DEFAULT_PRIORITY;

    public function __construct($loc)
    {
        $this->loc = $loc;
        $this->last_mod = new \DateTime();
    }

    /**
     * @return string
     */
    public function getLoc()
    {
        return $this->loc;
    }

    /**
     * @return \DateTime
     */
    public function getLastMod()
    {
        return clone $this->last_mod;
    }

    /**
     * @param \DateTime $last_mod
     *
     * @return SimpleUri
     */
    public function setLastMod(\DateTime $last_mod)
    {
        $this->last_mod = clone $last_mod;

        return $this;
    }

    /**
     * @return string
     */
    public function getChangeFreq()
    {
        return $this->change_freq;
    }

    /**
     * @param string $change_freq
     *
     * @return SimpleUri
     */
    public function setChangeFreq($change_freq)
    {
        $this->change_freq = $change_freq;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $priority
     *
     * @return SimpleUri
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }
}
