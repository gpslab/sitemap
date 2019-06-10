<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Unit\Url;

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
            [new \DateTimeImmutable('-10 minutes'), Url::CHANGE_FREQ_ALWAYS, '1.0'],
            [new \DateTimeImmutable('-1 hour'), Url::CHANGE_FREQ_HOURLY, '1.0'],
            [new \DateTimeImmutable('-1 day'), Url::CHANGE_FREQ_DAILY, '0.9'],
            [new \DateTimeImmutable('-1 week'), Url::CHANGE_FREQ_WEEKLY, '0.5'],
            [new \DateTimeImmutable('-1 month'), Url::CHANGE_FREQ_MONTHLY, '0.2'],
            [new \DateTimeImmutable('-1 year'), Url::CHANGE_FREQ_YEARLY, '0.1'],
            [new \DateTimeImmutable('-2 year'), Url::CHANGE_FREQ_NEVER, '0.0'],
        ];
    }

    /**
     * @dataProvider urls
     *
     * @param \DateTimeImmutable $last_mod
     * @param string             $change_freq
     * @param string             $priority
     */
    public function testCustomUrl(\DateTimeImmutable $last_mod, string $change_freq, string $priority): void
    {
        $loc = '/index.html';

        $url = new Url($loc, $last_mod, $change_freq, $priority);

        self::assertEquals($loc, $url->getLoc());
        self::assertEquals($last_mod, $url->getLastMod());
        self::assertEquals($change_freq, $url->getChangeFreq());
        self::assertEquals($priority, $url->getPriority());
    }
}
