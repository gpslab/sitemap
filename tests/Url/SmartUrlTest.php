<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Url;

use GpsLab\Component\Sitemap\Url\SmartUrl;

class SmartUrlTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultUrl()
    {
        $loc = '';
        $url = new SmartUrl($loc);

        $this->assertEquals($loc, $url->getLoc());
        $this->assertInstanceOf(\DateTimeImmutable::class, $url->getLastMod());
        $this->assertEquals(SmartUrl::CHANGE_FREQ_HOURLY, $url->getChangeFreq());
        $this->assertEquals(SmartUrl::DEFAULT_PRIORITY, $url->getPriority());
    }

    /**
     * @return array
     */
    public function urls()
    {
        return [
            [new \DateTimeImmutable('-10 minutes'), SmartUrl::CHANGE_FREQ_ALWAYS, '1.0'],
            [new \DateTimeImmutable('-1 hour'), SmartUrl::CHANGE_FREQ_HOURLY, '1.0'],
            [new \DateTimeImmutable('-1 day'), SmartUrl::CHANGE_FREQ_DAILY, '0.9'],
            [new \DateTimeImmutable('-1 week'), SmartUrl::CHANGE_FREQ_WEEKLY, '0.5'],
            [new \DateTimeImmutable('-1 month'), SmartUrl::CHANGE_FREQ_MONTHLY, '0.2'],
            [new \DateTimeImmutable('-1 year'), SmartUrl::CHANGE_FREQ_YEARLY, '0.1'],
            [new \DateTimeImmutable('-2 year'), SmartUrl::CHANGE_FREQ_NEVER, '0.0'],
        ];
    }

    /**
     * @dataProvider urls
     *
     * @param \DateTimeImmutable $last_mod
     * @param string             $change_freq
     * @param string             $priority
     */
    public function testCustomUrl(\DateTimeImmutable $last_mod, $change_freq, $priority)
    {
        $loc = '/';

        $url = new SmartUrl($loc, $last_mod, $change_freq, $priority);

        $this->assertEquals($loc, $url->getLoc());
        $this->assertEquals($last_mod, $url->getLastMod());
        $this->assertEquals($change_freq, $url->getChangeFreq());
        $this->assertEquals($priority, $url->getPriority());
    }

    /**
     * @return array
     */
    public function priorityOfLocations()
    {
        return [
            ['/', '1.0'],
            ['/index.html', '0.9'],
            ['/catalog', '0.9'],
            ['/catalog/123', '0.8'],
            ['/catalog/123/article', '0.7'],
            ['/catalog/123/article/456', '0.6'],
            ['/catalog/123/article/456/print', '0.5'],
            ['/catalog/123/subcatalog/789/article/456', '0.4'],
            ['/catalog/123/subcatalog/789/article/456/print', '0.3'],
            ['/catalog/123/subcatalog/789/article/456/print/foo', '0.2'],
            ['/catalog/123/subcatalog/789/article/456/print/foo/bar', '0.1'],
            ['/catalog/123/subcatalog/789/article/456/print/foo/bar/baz', '0.1'],
            ['/catalog/123/subcatalog/789/article/456/print/foo/bar/baz/qux', '0.1'],
        ];
    }

    /**
     * @dataProvider priorityOfLocations
     *
     * @param string $loc
     * @param string $priority
     */
    public function testSmartPriority($loc, $priority)
    {
        $url = new SmartUrl($loc);

        $this->assertEquals($loc, $url->getLoc());
        $this->assertEquals($priority, $url->getPriority());
    }

    /**
     * @return array
     */
    public function changeFreqOfLastMod()
    {
        return [
            [new \DateTimeImmutable('-1 year -1 day'), SmartUrl::CHANGE_FREQ_YEARLY],
            [new \DateTimeImmutable('-1 month -1 day'), SmartUrl::CHANGE_FREQ_MONTHLY],
            [new \DateTimeImmutable('-10 minutes'), SmartUrl::CHANGE_FREQ_HOURLY],
        ];
    }

    /**
     * @dataProvider changeFreqOfLastMod
     *
     * @param \DateTimeImmutable $last_mod
     * @param string             $change_freq
     */
    public function testSmartChangeFreqFromLastMod(\DateTimeImmutable $last_mod, $change_freq)
    {
        $loc = '/';
        $url = new SmartUrl($loc, $last_mod);

        $this->assertEquals($loc, $url->getLoc());
        $this->assertEquals($last_mod, $url->getLastMod());
        $this->assertEquals($change_freq, $url->getChangeFreq());
    }

    /**
     * @return array
     */
    public function changeFreqOfPriority()
    {
        return [
            ['1.0', SmartUrl::CHANGE_FREQ_HOURLY],
            ['0.9', SmartUrl::CHANGE_FREQ_DAILY],
            ['0.8', SmartUrl::CHANGE_FREQ_DAILY],
            ['0.7', SmartUrl::CHANGE_FREQ_WEEKLY],
            ['0.6', SmartUrl::CHANGE_FREQ_WEEKLY],
            ['0.5', SmartUrl::CHANGE_FREQ_WEEKLY],
            ['0.4', SmartUrl::CHANGE_FREQ_MONTHLY],
            ['0.3', SmartUrl::CHANGE_FREQ_MONTHLY],
            ['0.2', SmartUrl::CHANGE_FREQ_YEARLY],
            ['0.1', SmartUrl::CHANGE_FREQ_YEARLY],
            ['0.0', SmartUrl::CHANGE_FREQ_NEVER],
            ['-', SmartUrl::DEFAULT_CHANGE_FREQ],
        ];
    }

    /**
     * @dataProvider changeFreqOfPriority
     *
     * @param string|null $priority
     * @param string      $change_freq
     */
    public function testSmartChangeFreqFromPriority($priority, $change_freq)
    {
        $loc = '/';
        $url = new SmartUrl($loc, null, null, $priority);

        $this->assertEquals($loc, $url->getLoc());
        $this->assertInstanceOf(\DateTimeImmutable::class, $url->getLastMod());
        $this->assertEquals($change_freq, $url->getChangeFreq());
        $this->assertEquals($priority, $url->getPriority());
    }
}
