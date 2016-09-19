<?php
/**
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Component\Sitemap\Uri;

class SmartUri extends SimpleUri
{
    /**
     * @param string $loc
     */
    public function __construct($loc)
    {
        parent::__construct($loc);

        // set priority from loc
        if ($this->getPriority() == self::DEFAULT_PRIORITY) {
            $num = count(array_filter(explode('/', trim($loc, '/'))));
            if (!$num) {
                $this->setPriority('1.0');
            } elseif (($p = (10 - $num) / 10) > 0) {
                $this->setPriority('0.' . ($p * 10));
            } else {
                $this->setPriority('0.1');
            }
        }
    }

    /**
     * @param \DateTime $last_mod
     *
     * @return SmartUri
     */
    public function setLastMod(\DateTime $last_mod)
    {
        parent::setLastMod($last_mod);

        // set change freq from last mod
        if ($this->getChangeFreq() == self::DEFAULT_CHANGE_FREQ) {
            if ($last_mod < new \DateTime('-1 year')) {
                $this->setChangeFreq(self::CHANGE_FREQ_YEARLY);
            } elseif ($last_mod < new \DateTime('-1 month')) {
                $this->setChangeFreq(self::CHANGE_FREQ_MONTHLY);
            }
        }

        return $this;
    }

    /**
     * @param string $priority
     *
     * @return SmartUri
     */
    public function setPriority($priority)
    {
        parent::setPriority($priority);

        // set change freq from priority
        if ($this->getChangeFreq() == self::DEFAULT_CHANGE_FREQ && $priority == '1.0') {
            $this->setChangeFreq(self::CHANGE_FREQ_DAILY);
        }

        return $this;
    }
}
