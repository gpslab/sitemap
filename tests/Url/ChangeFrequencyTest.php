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
     * @return iterable<int, array<int, \DateTimeInterface|string>>
     */
    public function getChangeFrequencyOfLastModify(): iterable
    {
        $data = [
            '-1 year -1 day' => ChangeFrequency::YEARLY,
            '-1 month -1 day' => ChangeFrequency::MONTHLY,
            '-1 week -1 day' => ChangeFrequency::WEEKLY,
            '-1 days -2 hours' => ChangeFrequency::DAILY,
            '-20 hours' => ChangeFrequency::HOURLY,
            '-10 minutes' => ChangeFrequency::HOURLY,
        ];

        foreach ($data as $last_modify => $change_frequency) {
            yield [new \DateTimeImmutable($last_modify), $change_frequency];
            yield [new \DateTime($last_modify), $change_frequency];
        }
    }

    /**
     * @dataProvider getChangeFrequencyOfLastModify
     *
     * @param \DateTimeInterface $last_modify
     * @param string             $change_frequency
     */
    public function testGetChangeFrequencyByLastModify(\DateTimeInterface $last_modify, string $change_frequency): void
    {
        self::assertEquals($change_frequency, ChangeFrequency::createByLastModify($last_modify));
    }

    /**
     * @return array<int, array<int, string|int>>
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
