<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Unit\Stream;

use GpsLab\Component\Sitemap\Stream\LoggerStream;
use GpsLab\Component\Sitemap\Url\SmartUrl;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LoggerStreamTest extends TestCase
{
    /**
     * @var MockObject|LoggerInterface
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

        $url1 = new Url('/');
        $url2 = new SmartUrl('/');

        $this->logger
            ->expects(self::at(0))
            ->method('debug')
            ->with(sprintf('URL "%s" was added to sitemap.xml', $url1->getLoc()), [
                'changefreq' => $url1->getChangeFreq(),
                'lastmod' => $url1->getLastMod(),
                'priority' => $url1->getPriority(),
            ])
        ;
        $this->logger
            ->expects(self::at(1))
            ->method('debug')
            ->with(sprintf('URL "%s" was added to sitemap.xml', $url2->getLoc()), [
                'changefreq' => $url2->getChangeFreq(),
                'lastmod' => $url2->getLastMod(),
                'priority' => $url2->getPriority(),
            ])
        ;

        $this->stream->push($url1);
        $this->stream->push($url2);
    }
}
