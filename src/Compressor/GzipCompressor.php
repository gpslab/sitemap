<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Component\Sitemap\Compressor;

class GzipCompressor implements CompressorInterface
{
    /**
     * @param string $source
     * @param string $target
     *
     * @return bool
     */
    public function compress($source, $target = '')
    {
        $target = $target ?: $source.'.gz';
        $rh = @fopen($source, 'rb');
        $gz = @gzopen($target, 'w9');

        if ($rh === false || $gz === false) {
            return false;
        }

        while (!feof($rh)) {
            if (gzwrite($gz, fread($rh, 1024)) === false) {
                return false;
            }
        }

        fclose($rh);
        gzclose($gz);

        return true;
    }
}
