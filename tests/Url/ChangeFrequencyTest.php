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
use PHPUnit\Framework\TestCase;

class ChangeFrequencyTest extends TestCase
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
        self::assertEquals($change_frequency, ChangeFrequency::getByLastModify($last_modify));
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
            [11, null],
            [-1, null],
        ];
    }

    /**
     * @dataProvider getChangeFrequencyOfPriority
     *
     * @param int    $priority
     * @param string $change_frequency
     */
    public function testGetChangeFrequencyByPriority(int $priority, ?string $change_frequency): void
    {
        self::assertEquals($change_frequency, ChangeFrequency::getByPriority($priority));
    }

    /**
     * @return array<int, array<int, string|bool>>
     */
    public function getValidChangeFrequencies(): array
    {
        return [
            [ChangeFrequency::ALWAYS, true],
            [ChangeFrequency::HOURLY, true],
            [ChangeFrequency::DAILY, true],
            [ChangeFrequency::WEEKLY, true],
            [ChangeFrequency::MONTHLY, true],
            [ChangeFrequency::YEARLY, true],
            [ChangeFrequency::NEVER, true],
            ['-', false],
            ['', false],
        ];
    }

    /**
     * @dataProvider getValidChangeFrequencies
     *
     * @param string $change_frequency
     * @param bool   $is_valid
     */
    public function testIsValid(string $change_frequency, bool $is_valid): void
    {
        self::assertEquals($is_valid, ChangeFrequency::isValid($change_frequency));
    }
}
