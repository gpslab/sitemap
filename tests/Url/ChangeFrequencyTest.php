<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Url;

use GpsLab\Component\Sitemap\Url\ChangeFrequency;
use GpsLab\Component\Sitemap\Url\Exception\InvalidChangeFrequencyException;
use GpsLab\Component\Sitemap\Url\Priority;
use PHPUnit\Framework\TestCase;

final class ChangeFrequencyTest extends TestCase
{
    /**
     * @return array<int, array<int, \DateTimeInterface|string|null>>
     */
    public function getChangeFrequencyOfLastModify(): array
    {
        return [
            [new \DateTimeImmutable('-1 year -1 day'), ChangeFrequency::YEARLY],
            [new \DateTimeImmutable('-1 month -1 day'), ChangeFrequency::MONTHLY],
            [new \DateTimeImmutable('-1 week -1 day'), ChangeFrequency::WEEKLY],
            [new \DateTimeImmutable('-10 minutes'), null],
            [new \DateTime('-1 year -1 day'), ChangeFrequency::YEARLY],
            [new \DateTime('-1 month -1 day'), ChangeFrequency::MONTHLY],
            [new \DateTime('-1 week -1 day'), ChangeFrequency::WEEKLY],
            [new \DateTime('-10 minutes'), null],
        ];
    }

    /**
     * @dataProvider getChangeFrequencyOfLastModify
     *
     * @param \DateTimeInterface $last_modify
     * @param string             $change_frequency
     */
    public function testGetChangeFrequencyByLastModify(
        \DateTimeInterface $last_modify,
        ?string $change_frequency
    ): void {
        self::assertEquals($change_frequency, ChangeFrequency::createByLastModify($last_modify));
    }

    /**
     * @return array<int, array<int, string|int|null>>
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
    public function testCreateChangeFrequencyByPriority(int $priority, string $change_frequency): void
    {
        $object = ChangeFrequency::createByPriority(Priority::create($priority));

        self::assertSame($change_frequency, $object->getChangeFrequency());
        self::assertSame($change_frequency, (string) $object);
    }

    /**
     * @return string[][]
     */
    public function getValidChangeFrequencies(): array
    {
        return [
            [ChangeFrequency::ALWAYS],
            [ChangeFrequency::HOURLY],
            [ChangeFrequency::DAILY],
            [ChangeFrequency::WEEKLY],
            [ChangeFrequency::MONTHLY],
            [ChangeFrequency::YEARLY],
            [ChangeFrequency::NEVER],
        ];
    }

    /**
     * @dataProvider getValidChangeFrequencies
     *
     * @param string $change_frequency
     */
    public function testValid(string $change_frequency): void
    {
        $object = ChangeFrequency::create($change_frequency);

        self::assertSame($change_frequency, $object->getChangeFrequency());
        self::assertSame($change_frequency, (string) $object);

        self::assertSame($object, ChangeFrequency::create($change_frequency));
        self::assertSame($object, ChangeFrequency::$change_frequency());
    }

    /**
     * @return string[][]
     */
    public function getInvalidChangeFrequencies(): array
    {
        return [
            ['-'],
            [''],
        ];
    }

    /**
     * @dataProvider getInvalidChangeFrequencies
     *
     * @param string $change_frequency
     */
    public function testInvalid(string $change_frequency): void
    {
        $this->expectException(InvalidChangeFrequencyException::class);

        ChangeFrequency::create($change_frequency);
    }
}
