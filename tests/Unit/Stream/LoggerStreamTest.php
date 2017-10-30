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
use Psr\Log\LoggerInterface;

class LoggerStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var LoggerStream
     */
    private $stream;

    protected function setUp()
    {
        $this->logger = $this->getMock(LoggerInterface::class);

        $this->stream = new LoggerStream($this->logger);
    }

    public function testPush()
    {
        // do nothing
        $this->stream->open();
        $this->stream->close();

        $url1 = new Url('/');
        $url2 = new SmartUrl('/');

        $this->logger
            ->expects($this->at(0))
            ->method('debug')
            ->with(sprintf('URL "%s" is added to sitemap', $url1->getLoc()), [
                'changefreq' => $url1->getChangeFreq(),
                'lastmod' => $url1->getLastMod(),
                'priority' => $url1->getPriority(),
            ])
        ;
        $this->logger
            ->expects($this->at(1))
            ->method('debug')
            ->with(sprintf('URL "%s" is added to sitemap', $url2->getLoc()), [
                'changefreq' => $url2->getChangeFreq(),
                'lastmod' => $url2->getLastMod(),
                'priority' => $url2->getPriority(),
            ])
        ;

        $this->stream->push($url1);
        $this->stream->push($url2);

        $this->assertEquals(2, count($this->stream));
    }
}
