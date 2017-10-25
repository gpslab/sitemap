<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream;

use GpsLab\Component\Compressor\CompressorInterface;
use GpsLab\Component\Sitemap\Url\Url;

class CompressFileStream implements FileStream
{
    /**
     * @var FileStream
     */
    private $stream;

    /**
     * @var CompressorInterface
     */
    private $compressor;

    /**
     * @var string
     */
    private $filename = '';

    /**
     * @param FileStream          $stream
     * @param CompressorInterface $compressor
     * @param string              $filename
     */
    public function __construct(FileStream $stream, CompressorInterface $compressor, $filename)
    {
        $this->stream = $stream;
        $this->compressor = $compressor;
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    public function open()
    {
        $this->stream->open();
    }

    public function close()
    {
        $this->stream->close();
        $this->compressor->compress($this->stream->getFilename(), $this->filename);
    }

    /**
     * @param Url $url
     */
    public function push(Url $url)
    {
        $this->stream->push($url);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->stream->count();
    }
}
