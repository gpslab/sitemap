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

        $priority = Priority::createByLocation(new Location($location));
        $change_frequency = ChangeFrequency::createByPriority($priority);

        self::assertEquals($location, (string) $url->getLocation());
        self::assertNull($url->getLastModify());
        self::assertEquals($change_frequency, (string) $url->getChangeFrequency());
        self::assertSame($priority, $url->getPriority());
    }

    /**
     * @return array<int, array<int, \DateTimeInterface|string|int>>
     */
    public function getUrls(): array
    {
        return [
            [new \DateTimeImmutable('-10 minutes'), ChangeFrequency::ALWAYS, '1.0'],
            [new \DateTimeImmutable('-1 hour'), ChangeFrequency::HOURLY, '1.0'],
            [new \DateTimeImmutable('-1 day'), ChangeFrequency::DAILY, '0.9'],
            [new \DateTimeImmutable('-1 week'), ChangeFrequency::WEEKLY, '0.5'],
            [new \DateTimeImmutable('-1 month'), ChangeFrequency::MONTHLY, '0.2'],
            [new \DateTimeImmutable('-1 year'), ChangeFrequency::YEARLY, '0.1'],
            [new \DateTimeImmutable('-2 year'), ChangeFrequency::NEVER, '0.0'],
            [new \DateTime('-10 minutes'), ChangeFrequency::ALWAYS, '1.0'],
            [new \DateTime('-1 hour'), ChangeFrequency::HOURLY, '1.0'],
            [new \DateTime('-1 day'), ChangeFrequency::DAILY, '0.9'],
            [new \DateTime('-1 week'), ChangeFrequency::WEEKLY, '0.5'],
            [new \DateTime('-1 month'), ChangeFrequency::MONTHLY, '0.2'],
            [new \DateTime('-1 year'), ChangeFrequency::YEARLY, '0.1'],
            [new \DateTime('-2 year'), ChangeFrequency::NEVER, '0.0'],
        ];
    }

    /**
     * @dataProvider getUrls
     *
     * @param \DateTimeInterface $last_modify
     * @param string             $change_frequency
     * @param string             $priority
     */
    public function testCustomUrl(\DateTimeInterface $last_modify, string $change_frequency, string $priority): void
    {
        $location = '/';

        $url = new SmartUrl($location, $last_modify, $change_frequency, $priority);

        self::assertEquals($location, (string) $url->getLocation());
        self::assertEquals($last_modify, $url->getLastModify());
        self::assertEquals($change_frequency, (string) $url->getChangeFrequency());
        self::assertEquals($priority, (string) $url->getPriority());
    }

    /**
     * @return array<int, array<int, string|int>>
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

        self::assertEquals($location, (string) $url->getLocation());
        self::assertEquals($priority, (string) $url->getPriority());
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
        self::assertEquals($change_frequency, (string) $url->getChangeFrequency());
    }

    /**
     * @return array<int, array<int, int|string>>
     */
    public function getChangeFrequencyOfPriority(): array
    {
        return [
            ['1.0', ChangeFrequency::HOURLY],
            ['0.9', ChangeFrequency::DAILY],
            ['0.8', ChangeFrequency::DAILY],
            ['0.7', ChangeFrequency::WEEKLY],
            ['0.6', ChangeFrequency::WEEKLY],
            ['0.5', ChangeFrequency::WEEKLY],
            ['0.4', ChangeFrequency::MONTHLY],
            ['0.3', ChangeFrequency::MONTHLY],
            ['0.2', ChangeFrequency::YEARLY],
            ['0.1', ChangeFrequency::YEARLY],
            ['0.0', ChangeFrequency::NEVER],
        ];
    }

    /**
     * @dataProvider getChangeFrequencyOfPriority
     *
     * @param string $priority
     * @param string $change_frequency
     */
    public function testSmartChangeFrequencyFromPriority(string $priority, string $change_frequency): void
    {
        $location = '/';
        $url = new SmartUrl($location, null, null, $priority);

        self::assertEquals($location, (string) $url->getLocation());
        self::assertNull($url->getLastModify());
        self::assertEquals($change_frequency, (string) $url->getChangeFrequency());
        self::assertEquals($priority, (string) $url->getPriority());
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
