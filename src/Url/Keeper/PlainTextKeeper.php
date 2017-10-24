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

class PlainTextKeeper implements Keeper
{
    /**
     * @var string
     */
    private $filename = '';

    /**
     * @var string
     */
    private $content = '';

    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @param Url $url
     *
     * @return self
     */
    public function addUri(Url $url)
    {
        $this->content .= '<url>'.
                '<loc>'.htmlspecialchars($url->getLoc()).'</loc>'.
                '<lastmod>'.$url->getLastMod()->format('Y-m-d').'</lastmod>'.
                '<changefreq>'.$url->getChangeFreq().'</changefreq>'.
                '<priority>'.$url->getPriority().'</priority>'.
            '</url>';

        return $this;
    }

    /**
     * @return bool
     */
    public function save()
    {
        $content = '<?xml version="1.0" encoding="utf-8"?>'.
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.
            $this->content.
            '</urlset>';

        $this->content = '';

        return (bool) file_put_contents($this->filename, $content);
    }
}
