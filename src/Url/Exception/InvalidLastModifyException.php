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

final class InvalidLastModifyException extends InvalidArgumentException
{
    /**
     * @param \DateTimeInterface $last_modify
     *
     * @return InvalidLastModifyException
     */
    public static function lookToFuture(\DateTimeInterface $last_modify): self
    {
        return new self(sprintf(
            'The date "%s" of last URL modify should not look to future.',
            $last_modify->format('c')
        ));
    }
}
