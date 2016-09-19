<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Component\Sitemap\Compressor;

class Bzip2Compressor implements CompressorInterface
{
    /**
     * @param string $source
     * @param string $target
     *
     * @return bool
     */
    public function compress($source, $target = '')
    {
        $target = $target ?: $source.'.bz2';
        $rh = @fopen($source, 'rb');
        $bz = @bzopen($target, 'w9');

        if ($rh === false || $bz === false) {
            return false;
        }

        while (!feof($rh)) {
            if (bzwrite($bz, fread($rh, 1024)) === false) {
                return false;
            }
        }

        fclose($rh);
        bzclose($bz);

        return true;
    }
}
