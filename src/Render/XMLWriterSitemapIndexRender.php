<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011-2019, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Render;


class XMLWriterSitemapIndexRender implements SitemapIndexRender
{
    /**
     * @var \XMLWriter
     */
    private $writer;

    /**
     * @var string
     */
    private $host = '';

    /**
     * @var bool
     */
    private $use_indent = false;

    /**
     * @param string $host
     * @param bool   $use_indent
     */
    public function __construct(string $host, bool $use_indent = false)
    {
        $this->host = $host;
        $this->use_indent = $use_indent;
    }

    /**
     * @return string
     */
    public function start(): string
    {
        $this->writer = new \XMLWriter();
        $this->writer->openMemory();
        $this->writer->setIndent($this->use_indent);
        $this->writer->startDocument('1.0', 'UTF-8');
        $this->writer->startElement('sitemapindex');
        $this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        // XMLWriter expects that we can add more attributes
        // we force XMLWriter to set the closing bracket ">"
        $this->writer->text(PHP_EOL);

        return $this->writer->flush();
    }

    /**
     * @return string
     */
    public function end(): string
    {
        if (!$this->writer) {
            $this->start();
        }

        $this->writer->endElement();
        $end = $this->writer->flush();

        // the end string should end with eol
        if (!$this->use_indent) {
            $end .= PHP_EOL;
        }

        // restart the element for save indent in sitemaps added in future
        if ($this->use_indent) {
            $this->writer->startElement('sitemapindex');
            $this->writer->text(PHP_EOL);
            $this->writer->flush();
        }

        return $end;
    }

    /**
     * @param string                  $path
     * @param \DateTimeInterface|null $last_mod
     *
     * @return string
     */
    public function sitemap(string $path, \DateTimeInterface $last_mod = null): string
    {
        if (!$this->writer) {
            $this->start();
        }

        $this->writer->startElement('sitemap');
        $this->writer->writeElement('loc', $this->host.$path);
        if ($last_mod) {
            $this->writer->writeElement('lastmod', $last_mod->format('c'));
        }
        $this->writer->endElement();

        return $this->writer->flush();
    }
}
