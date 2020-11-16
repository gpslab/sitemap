<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Stream;

use GpsLab\Component\Sitemap\Stream\LoggerStream;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class LoggerStreamTest extends TestCase
{
    /**
     * @var MockObject&LoggerInterface
     */
    private $logger;

    /**
     * @var LoggerStream
     */
    private $stream;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->stream = new LoggerStream($this->logger);
    }

    public function testPush(): void
    {
        // do nothing
        $this->stream->open();
        $this->stream->close();

        $url1 = Url::create('https://example.com/');
        $url2 = Url::createSmart('https://example.com/');

        $this->logger
            ->expects(self::at(0))
            ->method('debug')
            ->with(sprintf('URL "%s" was added to sitemap.xml', $url1->getLocation()), [
                'changefreq' => (string) $url1->getChangeFrequency(),
                'lastmod' => $url1->getLastModify(),
                'priority' => (string) $url1->getPriority(),
            ])
        ;
        $this->logger
            ->expects(self::at(1))
            ->method('debug')
            ->with(sprintf('URL "%s" was added to sitemap.xml', $url2->getLocation()), [
                'changefreq' => (string) $url2->getChangeFrequency(),
                'lastmod' => $url2->getLastModify(),
                'priority' => (string) $url2->getPriority(),
            ])
        ;

        $this->stream->push($url1);
        $this->stream->push($url2);
    }
}
