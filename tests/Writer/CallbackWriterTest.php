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

use GpsLab\Component\Sitemap\Writer\CallbackWriter;
use PHPUnit\Framework\TestCase;

class CallbackWriterTest extends TestCase
{
    public function testWrite(): void
    {
        $content = [
            'foo',
            'bar',
        ];
        $calls = 0;
        $writer = new CallbackWriter(function($string) use (&$calls, $content) {
            $this->assertEquals($content[$calls], $string);
            ++$calls;
        });

        $writer->open(''); // not use filename
        foreach ($content as $string) {
            $writer->write($string);
        }
        $writer->close();

        $this->assertEquals(count($content), $calls);
    }
}
