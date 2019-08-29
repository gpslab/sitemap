<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Exception;

use GpsLab\Component\Sitemap\Url\ChangeFreq;

final class InvalidChangeFreqException extends InvalidArgumentException
{
    /**
     * @param string $change_freq
     *
     * @return InvalidChangeFreqException
     */
    public static function invalid(string $change_freq): self
    {
        return new self(sprintf(
            'You specify invalid change frequency "%s". Valid values are "%s".',
            $change_freq,
            implode('", "', ChangeFreq::AVAILABLE_CHANGE_FREQ)
        ));
    }
}
