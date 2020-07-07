<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream;

use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Url\Url;
use Psr\Log\LoggerInterface;

final class LoggerStream implements Stream
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function open(): void
    {
        // do nothing
    }

    public function close(): void
    {
        // do nothing
    }

    /**
     * @param Url $url
     *
     * @throws StreamStateException
     */
    public function push(Url $url): void
    {
        $this->logger->debug(sprintf('URL "%s" was added to sitemap.xml', $url->getLocation()), [
            'changefreq' => (string) $url->getChangeFrequency(),
            'lastmod' => $url->getLastModify(),
            'priority' => (string) $url->getPriority(),
        ]);
    }
}
