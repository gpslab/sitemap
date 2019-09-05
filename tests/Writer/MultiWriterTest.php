<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Tests\Writer;

use GpsLab\Component\Sitemap\Writer\MultiWriter;
use GpsLab\Component\Sitemap\Writer\Writer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MultiWriterTest extends TestCase
{
    private const FILENAME = '/var/www/sitemap.xml';

    /**
     * @return array
     */
    public function getWriters(): array
    {
        return [
            [
                [],
            ],
            [
                [
                    $this->createMock(Writer::class),
                ],
            ],
            [
                [
                    $this->createMock(Writer::class),
                    $this->createMock(Writer::class),
                ],
            ],
            [
                [
                    $this->createMock(Writer::class),
                    $this->createMock(Writer::class),
                    $this->createMock(Writer::class),
                ],
            ],
        ];
    }

    /**
     * @dataProvider getWriters
     *
     * @param MockObject[]|Writer[] $subwriters
     */
    public function testOpen(array $subwriters): void
    {
        $i = 0;
        $stream = new MultiWriter(...$subwriters);

        foreach ($subwriters as $subwriter) {
            $subwriter
                ->expects(self::once())
                ->method('start')
                ->with(self::FILENAME)
                ->willReturnCallback(static function () use (&$i) {
                    ++$i;
                })
            ;
        }

        $stream->start(self::FILENAME);

        self::assertEquals(count($subwriters), $i);
    }

    /**
     * @dataProvider getWriters
     *
     * @param MockObject[]|Writer[] $subwriters
     */
    public function testClose(array $subwriters): void
    {
        $i = 0;
        $stream = new MultiWriter(...$subwriters);

        foreach ($subwriters as $subwriter) {
            $subwriter
                ->expects(self::once())
                ->method('finish')
                ->willReturnCallback(static function () use (&$i) {
                    ++$i;
                })
            ;
        }

        $stream->finish();

        self::assertEquals(count($subwriters), $i);
    }

    /**
     * @dataProvider getWriters
     *
     * @param MockObject[]|Writer[] $subwriters
     */
    public function testAppend(array $subwriters): void
    {
        $i = 0;
        $contents = [
            'foo',
            'bar',
            'baz',
        ];

        $stream = new MultiWriter(...$subwriters);

        foreach ($subwriters as $subwriter) {
            foreach ($contents as $j => $content) {
                $subwriter
                    ->expects(self::at($j))
                    ->method('append')
                    ->with($content)
                    ->willReturnCallback(static function () use (&$i) {
                        ++$i;
                    })
                ;
            }
        }

        foreach ($contents as $content) {
            $stream->append($content);
        }

        self::assertEquals(count($subwriters) * count($contents), $i);
    }

    /**
     * @dataProvider getWriters
     *
     * @param MockObject[]|Writer[] $subwriters
     */
    public function testReset(array $subwriters): void
    {
        $i = 0;
        $content = 'foo';

        $stream = new MultiWriter(...$subwriters);
        foreach ($subwriters as $subwriter) {
            $subwriter
                ->expects(self::at(0))
                ->method('append')
                ->with($content)
                ->willReturnCallback(static function () use (&$i) {
                    ++$i;
                })
            ;
        }
        $stream->append($content);

        $stream->finish();

        self::assertEquals(count($subwriters), $i);
    }
}
