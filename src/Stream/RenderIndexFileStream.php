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
use GpsLab\Component\Sitemap\Stream\Exception\FileAccessException;
use GpsLab\Component\Sitemap\Stream\Exception\IndexStreamException;
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
     * @var FileStream
     */
    private $substream;

    /**
     * @var StreamState
     */
    private $state;

    /**
     * @var resource|null
     */
    private $handle;

    /**
     * @var string
     */
    private $host = '';

    /**
     * @var string
     */
    private $filename = '';

    /**
     * @var string
     */
    private $tmp_filename = '';

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var int
     */
    private $counter = 0;

    /**
     * @var bool
     */
    private $empty_index = true;

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

        $this->tmp_filename = tempnam(sys_get_temp_dir(), 'sitemap_index');
        if (($this->handle = @fopen($this->tmp_filename, 'wb')) === false) {
            throw FileAccessException::notWritable($this->tmp_filename);
        }

        fwrite($this->handle, $this->render->start());
    }

    public function close()
    {
        $this->state->close();
        $this->substream->close();

        // not add empty sitemap part to index
        if (!$this->empty_index) {
            $this->addSubStreamFileToIndex();
        }

        fwrite($this->handle, $this->render->end());
        fclose($this->handle);
        $filename = $this->substream->getFilename();

        // move part of the sitemap from the temporary directory to the target
        for ($i = 1; $i <= $this->index; ++$i) {
            $indexed_filename = $this->getIndexPartFilename($filename, $i);
            $source = sys_get_temp_dir().'/'.$indexed_filename;
            $target = dirname($this->filename).'/'.$indexed_filename;
            if (!rename($source, $target)) {
                throw IndexStreamException::failedRename($source, $target);
            }
        }

        // move the sitemap index file from the temporary directory to the target
        if (!rename($this->tmp_filename, $this->filename)) {
            unlink($this->tmp_filename);

            throw FileAccessException::failedOverwrite($this->tmp_filename, $this->filename);
        }

        // remove old parts of the sitemap from the target directory
        for ($i = $this->index + 1; true; ++$i) {
            $indexed_filename = $this->getIndexPartFilename($filename, $i);
            $target = dirname($this->filename).'/'.$indexed_filename;
            if (file_exists($target)) {
                unlink($target);
            } else {
                break;
            }
        }

        $this->handle = null;
        $this->tmp_filename = '';
        $this->counter = 0;
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
            $this->substream->close();
            $this->addSubStreamFileToIndex();
            $this->substream->open();
            $this->substream->push($url);
        }

        $this->empty_index = false;
        ++$this->counter;
    }

    private function addSubStreamFileToIndex()
    {
        $filename = $this->substream->getFilename();
        $indexed_filename = $this->getIndexPartFilename($filename, ++$this->index);
        $last_mod = (new \DateTimeImmutable())->setTimestamp(filemtime($filename));

        // rename sitemap file to sitemap part
        $new_filename = sys_get_temp_dir().'/'.$indexed_filename;
        if (!rename($filename, $new_filename)) {
            throw IndexStreamException::failedRename($filename, $new_filename);
        }

        fwrite($this->handle, $this->render->sitemap($indexed_filename, $last_mod));
    }

    /**
     * @param string $path
     * @param int    $index
     *
     * @return string
     */
    private function getIndexPartFilename($path, $index)
    {
        // use explode() for correct add index
        // sitemap.xml -> sitemap1.xml
        // sitemap.xml.gz -> sitemap1.xml.gz

        list($path, $extension) = explode('.', basename($path), 2) + ['', ''];

        return sprintf('%s%s.%s', $path, $index, $extension);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->counter;
    }
}
