<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Unit\Url;

use GpsLab\Component\Sitemap\Url\ChangeFreq;
use GpsLab\Component\Sitemap\Url\Url;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    public function testDefaultUrl(): void
    {
        $loc = '';
        $url = new Url($loc);

        self::assertEquals($loc, $url->getLoc());
        self::assertInstanceOf(\DateTimeImmutable::class, $url->getLastMod());
        self::assertEquals(Url::DEFAULT_CHANGE_FREQ, $url->getChangeFreq());
        self::assertEquals(Url::DEFAULT_PRIORITY, $url->getPriority());
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
        $loc = '/index.html';

        $url = new Url($loc, $last_mod, $change_freq, $priority);

        self::assertEquals($loc, $url->getLoc());
        self::assertEquals($last_mod, $url->getLastMod());
        self::assertEquals($change_freq, $url->getChangeFreq());
        self::assertEquals($priority, $url->getPriority());
    }
}
