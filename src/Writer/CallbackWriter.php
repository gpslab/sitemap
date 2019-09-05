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

class CallbackWriter implements Writer
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param string $filename
     */
    public function open(string $filename): void
    {
        // do nothing
    }

    /**
     * @param string $content
     */
    public function append(string $content): void
    {
        call_user_func($this->callback, $content);
    }

    public function finish(): void
    {
        // do nothing
    }
}
