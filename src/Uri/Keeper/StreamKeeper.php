<?php
/**
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Component\Sitemap\Uri\Keeper;

use GpsLab\Component\Sitemap\Uri\UriInterface;

class StreamKeeper implements KeeperInterface
{
    /**
     * @var string
     */
    protected $filename = '';

    /**
     * @var resource
     */
    protected $handle;

    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @param UriInterface $url
     *
     * @return self
     */
    public function addUri(UriInterface $url)
    {
        $this->start();

        fwrite(
            $this->handle,
            '<url>'.
                '<loc>'.htmlspecialchars($url->getLoc()).'</loc>'.
                '<lastmod>'.$url->getLastMod()->format('Y-m-d').'</lastmod>'.
                '<changefreq>'.$url->getChangeFreq().'</changefreq>'.
                '<priority>'.$url->getPriority().'</priority>'.
            '</url>'
        );

        return $this;
    }

    /**
     * @return bool
     */
    public function save()
    {
        $this->start();
        fwrite($this->handle, '</urlset>');
        return fclose($this->handle);
    }

    public function reset()
    {
        $this->handle = null;
    }

    protected function start()
    {
        if (!is_resource($this->handle)) {
            $this->handle = @fopen($this->filename, 'wb');
            if ($this->handle === false) {
                throw new \RuntimeException(sprintf('Failed to write file "%s".', $this->filename));
            }

            fwrite(
                $this->handle,
                '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL.
                '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            );
        }
    }
}
