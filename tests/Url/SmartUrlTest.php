<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Url;

use GpsLab\Component\Sitemap\Url\ChangeFreq;
use GpsLab\Component\Sitemap\Url\SmartUrl;
use PHPUnit\Framework\TestCase;

class SmartUrlTest extends TestCase
{
    public function testDefaultUrl(): void
    {
        $loc = '';
        $url = new SmartUrl($loc);

        self::assertEquals($loc, $url->getLoc());
        self::assertInstanceOf(\DateTimeImmutable::class, $url->getLastMod());
        self::assertEquals(ChangeFreq::HOURLY, $url->getChangeFreq());
        self::assertEquals(SmartUrl::DEFAULT_PRIORITY, $url->getPriority());
    }

    /**
     * @return array
     */
    public function urls(): array
    {
        return [
            [new \DateTimeImmutable('-10 minutes'), ChangeFreq::ALWAYS, '1.0'],
            [new \DateTimeImmutable('-1 hour'), ChangeFreq::HOURLY, '1.0'],
            [new \DateTimeImmutable('-1 day'), ChangeFreq::DAILY, '0.9'],
            [new \DateTimeImmutable('-1 week'), ChangeFreq::WEEKLY, '0.5'],
            [new \DateTimeImmutable('-1 month'), ChangeFreq::MONTHLY, '0.2'],
            [new \DateTimeImmutable('-1 year'), ChangeFreq::YEARLY, '0.1'],
            [new \DateTimeImmutable('-2 year'), ChangeFreq::NEVER, '0.0'],
            [new \DateTime('-10 minutes'), ChangeFreq::ALWAYS, '1.0'],
            [new \DateTime('-1 hour'), ChangeFreq::HOURLY, '1.0'],
            [new \DateTime('-1 day'), ChangeFreq::DAILY, '0.9'],
            [new \DateTime('-1 week'), ChangeFreq::WEEKLY, '0.5'],
            [new \DateTime('-1 month'), ChangeFreq::MONTHLY, '0.2'],
            [new \DateTime('-1 year'), ChangeFreq::YEARLY, '0.1'],
            [new \DateTime('-2 year'), ChangeFreq::NEVER, '0.0'],
        ];
    }

    /**
     * @dataProvider urls
     *
     * @param \DateTimeInterface $last_mod
     * @param string             $change_freq
     * @param string             $priority
     */
    public function testCustomUrl(\DateTimeInterface $last_mod, string $change_freq, string $priority): void
    {
        $loc = '/';

        $url = new SmartUrl($loc, $last_mod, $change_freq, $priority);

        self::assertEquals($loc, $url->getLoc());
        self::assertEquals($last_mod, $url->getLastMod());
        self::assertEquals($change_freq, $url->getChangeFreq());
        self::assertEquals($priority, $url->getPriority());
    }

    /**
     * @return array
     */
    public function priorityOfLocations(): array
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
    public function testSmartPriority(string $loc, string $priority): void
    {
        $url = new SmartUrl($loc);

        self::assertEquals($loc, $url->getLoc());
        self::assertEquals($priority, $url->getPriority());
    }

    /**
     * @return array
     */
    public function changeFreqOfLastMod(): array
    {
        return [
            [new \DateTimeImmutable('-1 year -1 day'), ChangeFreq::YEARLY],
            [new \DateTimeImmutable('-1 month -1 day'), ChangeFreq::MONTHLY],
            [new \DateTimeImmutable('-1 week -1 day'), ChangeFreq::WEEKLY],
            [new \DateTimeImmutable('-10 minutes'), ChangeFreq::HOURLY],
            [new \DateTime('-1 year -1 day'), ChangeFreq::YEARLY],
            [new \DateTime('-1 month -1 day'), ChangeFreq::MONTHLY],
            [new \DateTime('-1 week -1 day'), ChangeFreq::WEEKLY],
            [new \DateTime('-10 minutes'), ChangeFreq::HOURLY],
        ];
    }

    /**
     * @dataProvider changeFreqOfLastMod
     *
     * @param \DateTimeInterface $last_mod
     * @param string             $change_freq
     */
    public function testSmartChangeFreqFromLastMod(\DateTimeInterface $last_mod, string $change_freq): void
    {
        $loc = '/';
        $url = new SmartUrl($loc, $last_mod);

        self::assertEquals($loc, $url->getLoc());
        self::assertEquals($last_mod, $url->getLastMod());
        self::assertEquals($change_freq, $url->getChangeFreq());
    }

    /**
     * @return array
     */
    public function changeFreqOfPriority(): array
    {
        return [
            ['1.0', ChangeFreq::HOURLY],
            ['0.9', ChangeFreq::DAILY],
            ['0.8', ChangeFreq::DAILY],
            ['0.7', ChangeFreq::WEEKLY],
            ['0.6', ChangeFreq::WEEKLY],
            ['0.5', ChangeFreq::WEEKLY],
            ['0.4', ChangeFreq::MONTHLY],
            ['0.3', ChangeFreq::MONTHLY],
            ['0.2', ChangeFreq::YEARLY],
            ['0.1', ChangeFreq::YEARLY],
            ['0.0', ChangeFreq::NEVER],
            ['-', SmartUrl::DEFAULT_CHANGE_FREQ],
        ];
    }

    /**
     * @dataProvider changeFreqOfPriority
     *
     * @param string $priority
     * @param string $change_freq
     */
    public function testSmartChangeFreqFromPriority(string $priority, string $change_freq): void
    {
        $loc = '/';
        $url = new SmartUrl($loc, null, null, $priority);

        self::assertEquals($loc, $url->getLoc());
        self::assertInstanceOf(\DateTimeImmutable::class, $url->getLastMod());
        self::assertEquals($change_freq, $url->getChangeFreq());
        self::assertEquals($priority, $url->getPriority());
    }
}
