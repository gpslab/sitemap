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
     * @var string
     */
    private $buffer = '';

    /**
     * @param SitemapIndexRender $render
     * @param FileStream         $substream
     * @param string             $host
     * @param string             $filename
     */
    public function __construct(SitemapIndexRender $render, FileStream $substream, string $host, string $filename)
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
    public function getFilename(): string
    {
        return $this->filename;
    }

    public function open(): void
    {
        $this->state->open();
        $this->substream->open();
        $this->buffer = $this->render->start();
    }

    public function close(): void
    {
        $this->state->close();
        $this->addSubStreamFileToIndex();

        file_put_contents($this->filename, $this->buffer.$this->render->end());
        $this->buffer = '';
    }

    /**
     * @param Url $url
     */
    public function push(Url $url): void
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
    }

    private function addSubStreamFileToIndex(): void
    {
        $this->substream->close();

        $filename = $this->substream->getFilename();
        $indexed_filename = $this->getIndexPartFilename($filename, ++$this->index);
        $last_mod = (new \DateTimeImmutable())->setTimestamp(filemtime($filename));

        // rename sitemap file to the index part file
        rename($filename, dirname($filename).'/'.$indexed_filename);

        $this->buffer .= $this->render->sitemap($this->host.$indexed_filename, $last_mod);
    }

    /**
     * @param string $filename
     * @param int    $index
     *
     * @return string
     */
    private function getIndexPartFilename(string $filename, int $index): string
    {
        // use explode() for correct add index
        // sitemap.xml -> sitemap1.xml
        // sitemap.xml.gz -> sitemap1.xml.gz

        list($filename, $extension) = explode('.', basename($filename), 2);

        return sprintf('%s%s.%s', $filename, $index, $extension);
    }
}
