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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $counter = 0;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function open()
    {
        // do nothing
    }

    public function close()
    {
        // do nothing
    }

    /**
     * @param Url $url
     */
    public function push(Url $url)
    {
        $this->logger->debug(sprintf('URL "%s" is added to sitemap', $url->getLoc()), [
            'changefreq' => $url->getChangeFreq(),
            'lastmod' => $url->getLastMod(),
            'priority' => $url->getPriority(),
        ]);
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
