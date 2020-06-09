<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Writer;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Hook for PHPStan.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @param string $dir
     * @param string $prefix
     *
     * @return string
     */
    protected function tempnam(string $dir, string $prefix): string
    {
        $filename = tempnam($dir, $prefix);

        if ($filename === false) {
            throw new \RuntimeException(sprintf(
                'Failed create temporary file in "%s" folder with "%s" prefix.',
                $dir,
                $prefix
            ));
        }

        return $filename;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    protected function file_get_contents(string $filename): string
    {
        $content = file_get_contents($filename);

        if ($content === false) {
            throw new \RuntimeException(sprintf('Failed read content from "%s "file.', $filename));
        }

        return $content;
    }

    /**
     * @param int                $encoding
     * @param array<string, int> $options
     *
     * @return resource
     */
    protected function inflate_init(int $encoding, array $options = [])
    {
        $context = inflate_init($encoding, $options);

        if ($context === false) {
            throw new \RuntimeException(sprintf('Failed init inflate in "%d" encoding.', $encoding));
        }

        return $context;
    }

    /**
     * @param string $filename
     * @param string $mode
     *
     * @return resource
     */
    protected function gzopen(string $filename, string $mode)
    {
        $handle = gzopen($filename, $mode);

        if ($handle === false) {
            throw new \RuntimeException(sprintf('Failed open gzip "%s" file.', $filename));
        }

        return $handle;
    }
}
