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

final class XMLWriterSitemapRender implements SitemapRender
{
    /**
     * XMLWriter always use LF as new line character and on Windows too.
     */
    private const EOL = "\n";

    /**
     * @var \XMLWriter|null
     */
    private $writer;

    /**
     * @var bool
     */
    private $validating;

    /**
     * @var bool
     */
    private $use_indent;

    /**
     * @param bool $validating
     * @param bool $use_indent
     */
    public function __construct(bool $validating = true, bool $use_indent = false)
    {
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
        $this->writer->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');

        // XMLWriter expects that we can add more attributes
        // we force XMLWriter to set the closing bracket ">"
        $this->writer->text(self::EOL);

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
            $end .= self::EOL;
        }

        // restart the element for save indent in URLs added in future
        if ($this->use_indent) {
            $this->writer->startElement('urlset');
            $this->writer->text(self::EOL);
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
        $this->writer->writeElement('loc', (string) $url->getLocation());

        if ($url->getLastModify() instanceof \DateTimeInterface) {
            $this->writer->writeElement('lastmod', $url->getLastModify()->format('c'));
        }

        if ($url->getChangeFrequency()) {
            $this->writer->writeElement('changefreq', (string) $url->getChangeFrequency());
        }

        if ($url->getPriority() !== null) {
            $this->writer->writeElement('priority', (string) $url->getPriority());
        }

        foreach ($url->getLanguages() as $language) {
            $location = htmlspecialchars((string) $language->getLocation());

            $this->writer->startElement('xhtml:link');
            $this->writer->writeAttribute('rel', 'alternate');
            $this->writer->writeAttribute('hreflang', $language->getLanguage());
            $this->writer->writeAttribute('href', $location);
            $this->writer->endElement();
        }

        $this->writer->endElement();

        return $this->writer->flush();
    }
}
