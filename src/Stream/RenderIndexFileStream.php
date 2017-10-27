<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Stream;

use GpsLab\Component\Sitemap\Render\SitemapIndexRender;
use GpsLab\Component\Sitemap\Render\SitemapRender;
use GpsLab\Component\Sitemap\Stream\Exception\FileAccessException;
use GpsLab\Component\Sitemap\Stream\Exception\OverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\State\StreamState;
use GpsLab\Component\Sitemap\Url\Url;

class RenderIndexFileStream implements FileStream
{
    /**
     * @var SitemapIndexRender
     */
    private $render;

    /**
     * @var SitemapRender
     */
    private $substream;

    /**
     * @var \SplFileObject|null
     */
    private $file;

    /**
     * @var StreamState
     */
    private $state;

    /**
     * @var string
     */
    private $host = '';

    /**
     * @var string
     */
    private $filename = '';

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var int
     */
    private $counter = 0;

    /**
     * @param SitemapIndexRender $render
     * @param FileStream         $substream
     * @param string             $host
     * @param string             $filename
     */
    public function __construct(SitemapIndexRender $render, FileStream $substream, $host, $filename)
    {
        $this->render = $render;
        $this->substream = $substream;
        $this->host = $host;
        $this->filename = $filename;
        $this->state = new StreamState();

        if (substr($host, -1) != '/') {
            $this->host .= '/';
        }
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    public function open()
    {
        $this->state->open();
        $this->substream->open();
        $this->file = new \SplFileObject($this->filename, 'wb');

        if (!$this->file->isWritable()) {
            throw FileAccessException::notWritable($this->filename);
        }

        $this->write($this->render->start());
    }

    public function close()
    {
        $this->state->close();
        $this->addSubStreamFileToIndex();
        $this->write($this->render->end());
        unset($this->file);
    }

    /**
     * @param Url $url
     */
    public function push(Url $url)
    {
        if (!$this->state->isReady()) {
            throw StreamStateException::notReady();
        }

        try {
            $this->substream->push($url);
        } catch (OverflowException $e) {
            $this->addSubStreamFileToIndex();
            $this->substream->open();
        }

        ++$this->counter;
    }

    private function addSubStreamFileToIndex()
    {
        $this->substream->close();

        ++$this->index;
        $filename = $this->getIndexPartFilename();
        $last_mod = (new \DateTimeImmutable())->setTimestamp(filemtime($this->substream->getFilename()));

        // rename sitemap file to the index part file
        rename($this->substream->getFilename(), dirname($this->substream->getFilename()).'/'.$filename);

        $this->write($this->render->sitemap($this->host.$filename, $last_mod));
    }

    /**
     * @return string
     */
    private function getIndexPartFilename()
    {
        // use explode() for correct add index
        // sitemap.xml -> sitemap1.xml
        // sitemap.xml.gz -> sitemap1.xml.gz

        list($filename, $extension) = explode('.', $this->substream->getFilename(), 2);

        return sprintf('%s%s.%s', $filename, $this->index, $extension);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->counter;
    }

    /**
     * @param string $string
     */
    private function write($string)
    {
        $this->file->fwrite($string);
    }
}
