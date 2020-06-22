<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Writer\Exception;

final class ExtensionNotLoadedException extends \RuntimeException
{
    /**
     * @return self
     */
    public static function zlib(): self
    {
        return new self('The Zlib PHP extension is not loaded.');
    }
}
