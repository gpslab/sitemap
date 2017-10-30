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
     * @var int
     */
    private $counter = 0;

    /**
     * @param Stream $stream1
     * @param Stream $stream2
     * @param Stream ...
     */
    public function __construct(Stream $stream1, Stream $stream2)
    {
        foreach (func_get_args() as $stream) {
            $this->addStream($stream);
        }
    }

    /**
     * @param Stream $stream
     */
    private function addStream(Stream $stream)
    {
        $this->streams[] = $stream;
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
        ++$this->counter;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->counter;
    }
}
