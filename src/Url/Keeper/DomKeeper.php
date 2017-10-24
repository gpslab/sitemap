<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Keeper;

use GpsLab\Component\Sitemap\Url\Url;

class DomKeeper implements Keeper
{
    /**
     * @var string
     */
    private $filename = '';

    /**
     * @var \DOMDocument
     */
    private $doc;

    /**
     * @var \DOMElement
     */
    private $urlset;

    /**
     * @param \DOMDocument $doc
     * @param string $filename
     */
    public function __construct(\DOMDocument $doc, $filename)
    {
        $this->doc = $doc;
        $this->filename = $filename;

        $this->createUrlSet();
    }

    /**
     * @param Url $url
     *
     * @return self
     */
    public function addUri(Url $url)
    {
        $this->urlset->appendChild(
            $this->doc
                ->createElement('url')
                ->appendChild(
                    $this->doc->createElement('loc', htmlspecialchars($url->getLoc()))
                )->parentNode
                ->appendChild(
                    $this->doc->createElement('lastmod', $url->getLastMod()->format('Y-m-d'))
                )->parentNode
                ->appendChild(
                    $this->doc->createElement('changefreq', $url->getChangeFreq())
                )->parentNode
                ->appendChild(
                    $this->doc->createElement('priority', $url->getPriority())
                )->parentNode
        );

        return $this;
    }

    /**
     * @return bool
     */
    public function save()
    {
        $result = (bool) $this->doc->save($this->filename);

        $this->doc->removeChild($this->urlset);
        unset($this->urlset);

        $this->createUrlSet();

        return $result;
    }

    protected function createUrlSet()
    {
        $this->urlset = $this->doc->createElement('urlset');
        $this->urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $this->doc->appendChild($this->urlset);
    }
}
