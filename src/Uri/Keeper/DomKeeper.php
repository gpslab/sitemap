<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Component\Sitemap\Uri\Keeper;

use GpsLab\Component\Sitemap\Uri\UriInterface;

class DomKeeper implements KeeperInterface
{
    /**
     * @var string
     */
    protected $filename = '';

    /**
     * @var \DOMDocument
     */
    protected $doc;

    /**
     * @var \DOMElement
     */
    protected $urlset;

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
     * @param UriInterface $url
     *
     * @return self
     */
    public function addUri(UriInterface $url)
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
        $result = (bool)$this->doc->save($this->filename);

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
