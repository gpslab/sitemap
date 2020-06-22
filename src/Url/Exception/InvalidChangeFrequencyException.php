<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Exception;

use GpsLab\Component\Sitemap\Exception\InvalidArgumentException;
use GpsLab\Component\Sitemap\Url\ChangeFrequency;

final class InvalidChangeFrequencyException extends InvalidArgumentException
{
    /**
     * @param string $change_frequency
     *
     * @return self
     */
    public static function invalid(string $change_frequency): self
    {
        return new self(sprintf(
            'You specify invalid change frequency "%s". Valid values are "%s".',
            $change_frequency,
            implode('", "', ChangeFrequency::AVAILABLE_CHANGE_FREQUENCY)
        ));
    }
}
