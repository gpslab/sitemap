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
use Psr\Log\LoggerInterface;

class LoggerStream implements Stream
{
    /**
     * @var FileStream
     */
    private $stream;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Stream          $stream
     * @param LoggerInterface $logger
     */
    public function __construct(Stream $stream, LoggerInterface $logger)
    {
        $this->stream = $stream;
        $this->logger = $logger;
    }

    public function open()
    {
        $this->stream->open();
    }

    public function close()
    {
        $this->stream->close();
    }

    /**
     * @param Url $url
     */
    public function push(Url $url)
    {
        $this->stream->push($url);
        $this->logger->debug(sprintf('URL "%s" is added to sitemap', $url->getLoc()), [
            'changefreq' => $url->getChangeFreq(),
            'lastmod' => $url->getLastMod(),
            'priority' => $url->getPriority(),
        ]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->stream->count();
    }
}
