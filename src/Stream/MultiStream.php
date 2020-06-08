<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream;

use GpsLab\Component\Sitemap\Url\Url;

final class MultiStream implements Stream
{
    /**
     * @var Stream[]
     */
    private $streams;

    /**
     * @param Stream[] $streams
     */
    public function __construct(Stream ...$streams)
    {
        $this->streams = $streams;
    }

    public function open(): void
    {
        foreach ($this->streams as $stream) {
            $stream->open();
        }
    }

    public function close(): void
    {
        foreach ($this->streams as $stream) {
            $stream->close();
        }
    }

    /**
     * @param Url $url
     */
    public function push(Url $url): void
    {
        foreach ($this->streams as $stream) {
            $stream->push($url);
        }
    }
}
