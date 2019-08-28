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
    public function changeFreqOfLastMod(): array
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
     * @dataProvider changeFreqOfLastMod
     *
     * @param \DateTimeInterface $last_modify
     * @param string             $change_freq
     */
    public function testGetChangeFreqByLastMod(\DateTimeInterface $last_modify, ?string $change_freq): void
    {
        self::assertEquals($change_freq, ChangeFreq::getByLastMod($last_modify));
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
            ['-', null],
        ];
    }

    /**
     * @dataProvider changeFreqOfPriority
     *
     * @param string $priority
     * @param string $change_freq
     */
    public function testGetChangeFreqByPriority(string $priority, ?string $change_freq): void
    {
        self::assertEquals($change_freq, ChangeFreq::getByPriority($priority));
    }
}
