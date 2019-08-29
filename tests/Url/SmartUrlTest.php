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
use GpsLab\Component\Sitemap\Url\Exception\InvalidLocationException;
use GpsLab\Component\Sitemap\Url\Exception\InvalidChangeFreqException;
use GpsLab\Component\Sitemap\Url\Exception\InvalidPriorityException;
use GpsLab\Component\Sitemap\Url\Priority;
use GpsLab\Component\Sitemap\Url\SmartUrl;
use PHPUnit\Framework\TestCase;

class SmartUrlTest extends TestCase
{
    public function testDefaultUrl(): void
    {
        $location = '';
        $url = new SmartUrl($location);

        $priority = Priority::getByLocation($location);
        $change_freq = ChangeFreq::getByPriority($priority);

        self::assertEquals($location, $url->getLocation());
        self::assertNull($url->getLastModify());
        self::assertEquals($change_freq, $url->getChangeFreq());
        self::assertEquals($priority, $url->getPriority());
    }

    /**
     * @return array
     */
    public function getUrls(): array
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
     * @dataProvider getUrls
     *
     * @param \DateTimeInterface $last_modify
     * @param string             $change_freq
     * @param string             $priority
     */
    public function testCustomUrl(\DateTimeInterface $last_modify, string $change_freq, string $priority): void
    {
        $location = '/';

        $url = new SmartUrl($location, $last_modify, $change_freq, $priority);

        self::assertEquals($location, $url->getLocation());
        self::assertEquals($last_modify, $url->getLastModify());
        self::assertEquals($change_freq, $url->getChangeFreq());
        self::assertEquals($priority, $url->getPriority());
    }

    /**
     * @return array
     */
    public function getPriorityOfLocations(): array
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
     * @dataProvider getPriorityOfLocations
     *
     * @param string $location
     * @param string $priority
     */
    public function testSmartPriority(string $location, string $priority): void
    {
        $url = new SmartUrl($location);

        self::assertEquals($location, $url->getLocation());
        self::assertEquals($priority, $url->getPriority());
    }

    /**
     * @return array
     */
    public function getChangeFreqOfLastModify(): array
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
     * @dataProvider getChangeFreqOfLastModify
     *
     * @param \DateTimeInterface $last_modify
     * @param string             $change_freq
     */
    public function testSmartChangeFreqFromLastMod(\DateTimeInterface $last_modify, string $change_freq): void
    {
        $location = '/';
        $url = new SmartUrl($location, $last_modify);

        self::assertEquals($location, $url->getLocation());
        self::assertEquals($last_modify, $url->getLastModify());
        self::assertEquals($change_freq, $url->getChangeFreq());
    }

    /**
     * @return array
     */
    public function getChangeFreqOfPriority(): array
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
        ];
    }

    /**
     * @dataProvider getChangeFreqOfPriority
     *
     * @param string $priority
     * @param string $change_freq
     */
    public function testSmartChangeFreqFromPriority(string $priority, string $change_freq): void
    {
        $location = '/';
        $url = new SmartUrl($location, null, null, $priority);

        self::assertEquals($location, $url->getLocation());
        self::assertNull($url->getLastModify());
        self::assertEquals($change_freq, $url->getChangeFreq());
        self::assertEquals($priority, $url->getPriority());
    }

    /**
     * @return array
     */
    public function getInvalidLocations(): array
    {
        return [
            ['../'],
            ['index.html'],
            ['&foo=bar'],
            ['№'],
            ['@'],
            ['\\'],
        ];
    }

    /**
     * @dataProvider getInvalidLocations
     *
     * @param string $location
     */
    public function testInvalidLocation(string $location): void
    {
        $this->expectException(InvalidLocationException::class);

        new SmartUrl($location);
    }

    /**
     * @return array
     */
    public function getValidLocations(): array
    {
        return [
            [''],
            ['/'],
            ['#about'],
            ['?foo=bar'],
            ['?foo=bar&baz=123'],
            ['/index.html'],
            ['/about/index.html'],
        ];
    }

    /**
     * @dataProvider getValidLocations
     *
     * @param string $location
     */
    public function testValidLocation(string $location): void
    {
        $this->assertEquals($location, (new SmartUrl($location))->getLocation());
    }

    public function testInvalidPriority(): void
    {
        $this->expectException(InvalidPriorityException::class);

        new SmartUrl('/', null, null, '');
    }

    public function testInvalidChangeFreq(): void
    {
        $this->expectException(InvalidChangeFreqException::class);

        new SmartUrl('/', null, '');
    }
}
