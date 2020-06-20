<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Url;

use GpsLab\Component\Sitemap\Exception\InvalidLocationException;
use GpsLab\Component\Sitemap\Location;
use GpsLab\Component\Sitemap\Url\ChangeFrequency;
use GpsLab\Component\Sitemap\Url\Exception\InvalidChangeFrequencyException;
use GpsLab\Component\Sitemap\Url\Exception\InvalidLastModifyException;
use GpsLab\Component\Sitemap\Url\Exception\InvalidPriorityException;
use GpsLab\Component\Sitemap\Url\Priority;
use GpsLab\Component\Sitemap\Url\SmartUrl;
use PHPUnit\Framework\TestCase;

final class SmartUrlTest extends TestCase
{
    public function testDefaultUrl(): void
    {
        $location = '';
        $url = new SmartUrl($location);

        $priority = Priority::getByLocation(new Location($location));
        $change_frequency = ChangeFrequency::getByPriority($priority);

        self::assertEquals($location, (string) $url->getLocation());
        self::assertNull($url->getLastModify());
        self::assertEquals($change_frequency, $url->getChangeFrequency());
        self::assertEquals($priority, $url->getPriority());
    }

    /**
     * @return array<int, array<int, \DateTimeInterface|string|int>>
     */
    public function getUrls(): array
    {
        return [
            [new \DateTimeImmutable('-10 minutes'), ChangeFrequency::ALWAYS, 10],
            [new \DateTimeImmutable('-1 hour'), ChangeFrequency::HOURLY, 10],
            [new \DateTimeImmutable('-1 day'), ChangeFrequency::DAILY, 9],
            [new \DateTimeImmutable('-1 week'), ChangeFrequency::WEEKLY, 5],
            [new \DateTimeImmutable('-1 month'), ChangeFrequency::MONTHLY, 2],
            [new \DateTimeImmutable('-1 year'), ChangeFrequency::YEARLY, 1],
            [new \DateTimeImmutable('-2 year'), ChangeFrequency::NEVER, 0],
            [new \DateTime('-10 minutes'), ChangeFrequency::ALWAYS, 10],
            [new \DateTime('-1 hour'), ChangeFrequency::HOURLY, 10],
            [new \DateTime('-1 day'), ChangeFrequency::DAILY, 9],
            [new \DateTime('-1 week'), ChangeFrequency::WEEKLY, 5],
            [new \DateTime('-1 month'), ChangeFrequency::MONTHLY, 2],
            [new \DateTime('-1 year'), ChangeFrequency::YEARLY, 1],
            [new \DateTime('-2 year'), ChangeFrequency::NEVER, 0],
        ];
    }

    /**
     * @dataProvider getUrls
     *
     * @param \DateTimeInterface $last_modify
     * @param string             $change_frequency
     * @param int                $priority
     */
    public function testCustomUrl(\DateTimeInterface $last_modify, string $change_frequency, int $priority): void
    {
        $location = '/';

        $url = new SmartUrl($location, $last_modify, $change_frequency, $priority);

        self::assertEquals($location, (string) $url->getLocation());
        self::assertEquals($last_modify, $url->getLastModify());
        self::assertEquals($change_frequency, $url->getChangeFrequency());
        self::assertEquals($priority, $url->getPriority());
    }

    /**
     * @return array<int, array<int, string|int>>
     */
    public function getPriorityOfLocations(): array
    {
        return [
            ['/', 10],
            ['/index.html', 9],
            ['/catalog', 9],
            ['/catalog/123', 8],
            ['/catalog/123/article', 7],
            ['/catalog/123/article/456', 6],
            ['/catalog/123/article/456/print', 5],
            ['/catalog/123/subcatalog/789/article/456', 4],
            ['/catalog/123/subcatalog/789/article/456/print', 3],
            ['/catalog/123/subcatalog/789/article/456/print/foo', 2],
            ['/catalog/123/subcatalog/789/article/456/print/foo/bar', 1],
            ['/catalog/123/subcatalog/789/article/456/print/foo/bar/baz', 1],
            ['/catalog/123/subcatalog/789/article/456/print/foo/bar/baz/qux', 1],
        ];
    }

    /**
     * @dataProvider getPriorityOfLocations
     *
     * @param string $location
     * @param float  $priority
     */
    public function testSmartPriority(string $location, float $priority): void
    {
        $url = new SmartUrl($location);

        self::assertEquals($location, (string) $url->getLocation());
        self::assertEquals($priority, $url->getPriority());
    }

    /**
     * @return array<int, array<int, \DateTimeInterface|string>>
     */
    public function getChangeFrequencyOfLastModify(): array
    {
        return [
            [new \DateTimeImmutable('-1 year -1 day'), ChangeFrequency::YEARLY],
            [new \DateTimeImmutable('-1 month -1 day'), ChangeFrequency::MONTHLY],
            [new \DateTimeImmutable('-1 week -1 day'), ChangeFrequency::WEEKLY],
            [new \DateTimeImmutable('-10 minutes'), ChangeFrequency::HOURLY],
            [new \DateTime('-1 year -1 day'), ChangeFrequency::YEARLY],
            [new \DateTime('-1 month -1 day'), ChangeFrequency::MONTHLY],
            [new \DateTime('-1 week -1 day'), ChangeFrequency::WEEKLY],
            [new \DateTime('-10 minutes'), ChangeFrequency::HOURLY],
        ];
    }

    /**
     * @dataProvider getChangeFrequencyOfLastModify
     *
     * @param \DateTimeInterface $last_modify
     * @param string             $change_frequency
     */
    public function testSmartChangeFrequencyFromLastMod(
        \DateTimeInterface $last_modify,
        string $change_frequency
    ): void {
        $location = '/';
        $url = new SmartUrl($location, $last_modify);

        self::assertEquals($location, (string) $url->getLocation());
        self::assertEquals($last_modify, $url->getLastModify());
        self::assertEquals($change_frequency, $url->getChangeFrequency());
    }

    /**
     * @return array<int, array<int, int|string>>
     */
    public function getChangeFrequencyOfPriority(): array
    {
        return [
            [10, ChangeFrequency::HOURLY],
            [9, ChangeFrequency::DAILY],
            [8, ChangeFrequency::DAILY],
            [7, ChangeFrequency::WEEKLY],
            [6, ChangeFrequency::WEEKLY],
            [5, ChangeFrequency::WEEKLY],
            [4, ChangeFrequency::MONTHLY],
            [3, ChangeFrequency::MONTHLY],
            [2, ChangeFrequency::YEARLY],
            [1, ChangeFrequency::YEARLY],
            [0, ChangeFrequency::NEVER],
        ];
    }

    /**
     * @dataProvider getChangeFrequencyOfPriority
     *
     * @param int    $priority
     * @param string $change_frequency
     */
    public function testSmartChangeFrequencyFromPriority(int $priority, string $change_frequency): void
    {
        $location = '/';
        $url = new SmartUrl($location, null, null, $priority);

        self::assertEquals($location, (string) $url->getLocation());
        self::assertNull($url->getLastModify());
        self::assertEquals($change_frequency, $url->getChangeFrequency());
        self::assertEquals($priority, $url->getPriority());
    }

    /**
     * @return string[][]
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
     * @return string[][]
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
        $this->assertEquals($location, (string) (new SmartUrl($location))->getLocation());
    }

    public function testInvalidLastModify(): void
    {
        $this->expectException(InvalidLastModifyException::class);

        new SmartUrl('/', new \DateTimeImmutable('+1 minutes'));
    }

    public function testInvalidPriority(): void
    {
        $this->expectException(InvalidPriorityException::class);

        new SmartUrl('/', null, null, 11);
    }

    public function testInvalidChangeFrequency(): void
    {
        $this->expectException(InvalidChangeFrequencyException::class);

        new SmartUrl('/', null, '');
    }
}
