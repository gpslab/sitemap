<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Writer;

class MultiWriter implements Writer
{
    /**
     * @var Writer[]
     */
    private $writers;

    /**
     * @param Writer[] $writers
     */
    public function __construct(Writer ...$writers)
    {
        $this->writers = $writers;
    }

    /**
     * @param string $filename
     */
    public function start(string $filename): void
    {
        foreach ($this->writers as $writer) {
            $writer->start($filename);
        }
    }

    /**
     * @param string $content
     */
    public function append(string $content): void
    {
        foreach ($this->writers as $writer) {
            $writer->append($content);
        }
    }

    public function finish(): void
    {
        foreach ($this->writers as $writer) {
            $writer->finish();
        }
    }
}
