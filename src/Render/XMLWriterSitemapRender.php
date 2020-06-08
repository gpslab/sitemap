<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
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
     * @var string
     */
    private $web_path;

    /**
     * @var bool
     */
    private $validating;

    /**
     * @var bool
     */
    private $use_indent;

    /**
     * @param string $web_path
     * @param bool   $validating
     * @param bool   $use_indent
     */
    public function __construct(string $web_path, bool $validating = true, bool $use_indent = false)
    {
        $this->web_path = $web_path;
        $this->validating = $validating;
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
        if ($this->validating) {
            $this->writer->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            $this->writer->writeAttribute('xsi:schemaLocation', implode(' ', [
                'http://www.sitemaps.org/schemas/sitemap/0.9',
                'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd',
            ]));
        }
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
        $this->writer->writeElement('loc', $this->web_path.$url->getLocation());
        if ($url->getLastModify() instanceof \DateTimeInterface) {
            $this->writer->writeElement('lastmod', $url->getLastModify()->format('c'));
        }
        if ($url->getChangeFrequency() !== null) {
            $this->writer->writeElement('changefreq', $url->getChangeFrequency());
        }
        if ($url->getPriority() !== null) {
            $this->writer->writeElement('priority', number_format($url->getPriority() / 10, 1));
        }
        $this->writer->endElement();

        return $this->writer->flush();
    }
}
