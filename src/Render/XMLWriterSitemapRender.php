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

use GpsLab\Component\Sitemap\Url\Url;

class XMLWriterSitemapRender implements SitemapRender
{
    /**
     * @var \XMLWriter
     */
    private $writer;

    /**
     * @var bool
     */
    private $use_indent;

    /**
     * @param bool $use_indent
     */
    public function __construct(bool $use_indent = false)
    {
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
        $this->writer->startElement('urlset');
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

        // restart the element for save indent in URLs added in future
        if ($this->use_indent) {
            $this->writer->startElement('urlset');
            $this->writer->text(PHP_EOL);
            $this->writer->flush();
        }

        return $end;
    }

    /**
     * @param Url $url
     *
     * @return string
     */
    public function url(Url $url): string
    {
        if (!$this->writer) {
            $this->start();
        }

        $this->writer->startElement('url');
        $this->writer->writeElement('loc', $url->getLoc());
        $this->writer->writeElement('lastmod', $url->getLastMod()->format('c'));
        $this->writer->writeElement('changefreq', $url->getChangeFreq());
        $this->writer->writeElement('priority', $url->getPriority());
        $this->writer->endElement();

        return $this->writer->flush();
    }
}
