<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream;

use GpsLab\Component\Sitemap\Url\Url;

class MultiStream implements Stream
{
    /**
     * @var Stream[]
     */
    private $streams = [];

    /**
     * @param Stream ...$streams
     */
    public function __construct(Stream ...$streams)
    {
        $this->streams = $streams;
    }

    public function open()
    {
        foreach ($this->streams as $stream) {
            $stream->open();
        }
    }

    public function close()
    {
        foreach ($this->streams as $stream) {
            $stream->close();
        }
    }

    /**
     * @param Url $url
     */
    public function push(Url $url)
    {
        foreach ($this->streams as $stream) {
            $stream->push($url);
        }
    }
}
