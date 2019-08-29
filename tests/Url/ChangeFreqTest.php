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
use PHPUnit\Framework\TestCase;

class ChangeFreqTest extends TestCase
{
    /**
     * @return array
     */
    public function getChangeFreqOfLastModify(): array
    {
        return [
            [new \DateTimeImmutable('-1 year -1 day'), ChangeFreq::YEARLY],
            [new \DateTimeImmutable('-1 month -1 day'), ChangeFreq::MONTHLY],
            [new \DateTimeImmutable('-1 week -1 day'), ChangeFreq::WEEKLY],
            [new \DateTimeImmutable('-10 minutes'), null],
            [new \DateTime('-1 year -1 day'), ChangeFreq::YEARLY],
            [new \DateTime('-1 month -1 day'), ChangeFreq::MONTHLY],
            [new \DateTime('-1 week -1 day'), ChangeFreq::WEEKLY],
            [new \DateTime('-10 minutes'), null],
        ];
    }

    /**
     * @dataProvider getChangeFreqOfLastModify
     *
     * @param \DateTimeInterface $last_modify
     * @param string             $change_freq
     */
    public function testGetChangeFreqByLastModify(\DateTimeInterface $last_modify, ?string $change_freq): void
    {
        self::assertEquals($change_freq, ChangeFreq::getByLastModify($last_modify));
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
            ['-', null],
        ];
    }

    /**
     * @dataProvider getChangeFreqOfPriority
     *
     * @param string $priority
     * @param string $change_freq
     */
    public function testGetChangeFreqByPriority(string $priority, ?string $change_freq): void
    {
        self::assertEquals($change_freq, ChangeFreq::getByPriority($priority));
    }

    /**
     * @return array
     */
    public function getValidChangeFrequencies(): array
    {
        return [
            [ChangeFreq::ALWAYS, true],
            [ChangeFreq::HOURLY, true],
            [ChangeFreq::DAILY, true],
            [ChangeFreq::WEEKLY, true],
            [ChangeFreq::MONTHLY, true],
            [ChangeFreq::YEARLY, true],
            [ChangeFreq::NEVER, true],
            ['-', false],
            ['', false],
        ];
    }

    /**
     * @dataProvider getValidChangeFrequencies
     *
     * @param string $priority
     * @param bool   $is_valid
     */
    public function testIsValid(string $priority, bool $is_valid): void
    {
        self::assertEquals($is_valid, ChangeFreq::isValid($priority));
    }
}
