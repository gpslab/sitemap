<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Component\Sitemap\Uri;

interface UriInterface
{
    const CHANGE_FREQ_ALWAYS = 'always';
    const CHANGE_FREQ_HOURLY = 'hourly';
    const CHANGE_FREQ_DAILY = 'daily';
    const CHANGE_FREQ_WEEKLY = 'weekly';
    const CHANGE_FREQ_MONTHLY = 'monthly';
    const CHANGE_FREQ_YEARLY = 'yearly';
    const CHANGE_FREQ_NEVER = 'never';

    const DEFAULT_PRIORITY = '1.0';

    const DEFAULT_CHANGE_FREQ = self::CHANGE_FREQ_WEEKLY;

    /**
     * @param string $loc
     */
    public function __construct($loc);

    /**
     * @return string
     */
    public function getLoc();

    /**
     * @return \DateTime
     */
    public function getLastMod();

    /**
     * @param \DateTime $last_mod
     *
     * @return UriInterface
     */
    public function setLastMod(\DateTime $last_mod);

    /**
     * @return string
     */
    public function getChangeFreq();

    /**
     * @param string $change_freq
     *
     * @return UriInterface
     */
    public function setChangeFreq($change_freq);

    /**
     * @return string
     */
    public function getPriority();

    /**
     * @param string $priority
     *
     * @return UriInterface
     */
    public function setPriority($priority);
}
