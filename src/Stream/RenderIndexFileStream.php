<?php
/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */

namespace GpsLab\Component\Sitemap\Url\Aggregator;

use GpsLab\Component\Sitemap\Render\SitemapIndexRender;
use GpsLab\Component\Sitemap\Render\SitemapRender;
use GpsLab\Component\Sitemap\Stream\Exception\OverflowException;
use GpsLab\Component\Sitemap\Stream\Exception\StreamStateException;
use GpsLab\Component\Sitemap\Stream\FileStream;
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
    private $sub_stream;

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
     * @param FileStream         $sub_stream
     * @param string             $host
     * @param string             $filename
     */
    public function __construct(SitemapIndexRender $render, FileStream $sub_stream, $host, $filename)
    {
        $this->render = $render;
        $this->sub_stream = $sub_stream;
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
        $this->sub_stream->open();
        $this->file = new \SplFileObject($this->filename, 'wb');
        $this->file->fwrite($this->render->start());
    }

    public function close()
    {
        $this->state->close();
        $this->addSubStreamFileToIndex();
        $this->file->fwrite($this->render->end());
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
            $this->sub_stream->push($url);
        } catch (OverflowException $e) {
            $this->addSubStreamFileToIndex();
            $this->sub_stream->open();
        }

        ++$this->counter;
    }

    private function addSubStreamFileToIndex()
    {
        $this->sub_stream->close();

        ++$this->index;
        $filename = $this->getIndexPartFilename();
        $last_mod = (new \DateTimeImmutable())->setTimestamp(filemtime($this->sub_stream->getFilename()));

        // rename sitemap file to the index part file
        rename($this->sub_stream->getFilename(), dirname($this->sub_stream->getFilename()).'/'.$filename);

        $this->file->fwrite($this->render->sitemap($this->host.$filename, $last_mod));
    }

    /**
     * @return string
     */
    private function getIndexPartFilename()
    {
        // use explode() for correct add index
        // sitemap.xml -> sitemap1.xml
        // sitemap.xml.gz -> sitemap1.xml.gz

        list($filename, $extension) = explode('.', $this->sub_stream->getFilename(), 2);

        return sprintf('%s%s.%s', $filename, $this->index, $extension);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->counter;
    }
}
