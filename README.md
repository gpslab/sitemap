[![Latest Stable Version](https://img.shields.io/packagist/v/gpslab/sitemap.svg?maxAge=3600&label=stable)](https://packagist.org/packages/gpslab/sitemap)
![PHP from Travis config](https://img.shields.io/travis/php-v/gpslab/sitemap.svg?maxAge=3600)
[![Build Status](https://img.shields.io/travis/gpslab/sitemap.svg?maxAge=3600)](https://travis-ci.org/gpslab/sitemap)
[![Coverage Status](https://img.shields.io/coveralls/gpslab/sitemap.svg?maxAge=3600)](https://coveralls.io/github/gpslab/sitemap?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/gpslab/sitemap.svg?maxAge=3600)](https://scrutinizer-ci.com/g/gpslab/sitemap/?branch=master)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/b02448c4-54f2-4afb-897f-48a4ab9d37cf.svg?maxAge=3600&label=SLInsight)](https://insight.sensiolabs.com/projects/b02448c4-54f2-4afb-897f-48a4ab9d37cf)
[![StyleCI](https://styleci.io/repos/68381260/shield?branch=master)](https://styleci.io/repos/68381260)
[![License](https://img.shields.io/packagist/l/gpslab/sitemap.svg?maxAge=3600)](https://github.com/gpslab/sitemap)

sitemap.xml builder
===================

This is a complex of services for build Sitemaps.xml and index of Sitemap.xml files.

See [protocol](https://www.sitemaps.org/protocol.html) for more details.

![Example build sitemap.xml](build.png)

## Installation

Pretty simple with [Composer](http://packagist.org), run:

```sh
composer require gpslab/sitemap
```

## Simple usage

```php
// URLs on your site
$urls = [
   new Url(
       '/', // loc
       new \DateTimeImmutable('-10 minutes'), // lastmod
       ChangeFrequency::ALWAYS, // changefreq
       10 // priority
   ),
   new Url(
       '/contacts.html',
       new \DateTimeImmutable('-1 month'),
       ChangeFrequency::MONTHLY,
       7
   ),
   new Url(
       '/about.html',
       new \DateTimeImmutable('-2 month'),
       ChangeFrequency::MONTHLY,
       7
   ),
];

// the file into which we will write our sitemap
$filename = __DIR__.'/sitemap.xml';

// web path to pages on your site
$web_path = 'https://example.com';

// configure streamer
$render = new PlainTextSitemapRender($web_path);
$writer = new TempFileWriter();
$stream = new WritingStream($render, $writer, $filename);

// build sitemap.xml
$stream->open();
foreach ($urls as $url) {
    $stream->push($url);
}
$stream->close();
```

## URL builders

You can create a service that will return a links to pages of your site.

```php
class MySiteUrlBuilder implements UrlBuilder
{
    public function getIterator(): \Traversable
    {
        // add URLs on your site
        return new \ArrayIterator([
          new Url(
              '/', // loc
              new \DateTimeImmutable('-10 minutes'), // lastmod
              ChangeFrequency::ALWAYS, // changefreq
              10 // priority
          ),
          new Url(
              '/contacts.html',
              new \DateTimeImmutable('-1 month'),
              ChangeFrequency::MONTHLY,
              7
          ),
          new Url(
              '/about.html',
              new \DateTimeImmutable('-2 month'),
              ChangeFrequency::MONTHLY,
              7
          ),
       ]);
    }
}
```

It was a simple build. We add a builder more complicated.

```php
class ArticlesUrlBuilder implements UrlBuilder
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getIterator(): \Traversable
    {
        $section_update_at = null;
        $sth = $this->pdo->query('SELECT id, update_at FROM article');
        $sth->execute();

        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $update_at = new \DateTimeImmutable($row['update_at']);
            $section_update_at = max($section_update_at, $update_at);

            // SmartUrl automatically fills fields that it can
            yield new SmartUrl(
                sprintf('/article/%d', $row['id']),
                $update_at
            );
        }

        // link to section
        yield new Url(
            '/article/',
            $section_update_at ?: new \DateTimeImmutable('-1 day'),
            ChangeFrequency::DAILY,
            9
        );
    }
}
```

We take one of the exists builders and configure it.

```php
// collect a collection of builders
$builders = new MultiUrlBuilder([
    new MySiteUrlBuilder(),
    new ArticlesUrlBuilder(/* $pdo */),
]);

// the file into which we will write our sitemap
$filename = __DIR__.'/sitemap.xml';

// web path to pages on your site
$web_path = 'https://example.com';

// configure streamer
$render = new PlainTextSitemapRender($web_path);
$writer = new TempFileWriter();
$stream = new WritingStream($render, $writer, $filename);

// build sitemap.xml
$stream->open();
foreach ($builders as $url) {
    $stream->push($url);
}
$stream->close();
```

## Sitemap index

You can create [Sitemap index](https://www.sitemaps.org/protocol.html#index) to group multiple sitemap files.

```php
// collect a collection of builders
$builders = new MultiUrlBuilder([
    new MySiteUrlBuilder(),
    new ArticlesUrlBuilder(/* $pdo */),
]);

// the file into which we will write our sitemap
$index_filename = __DIR__.'/sitemap.xml';

// web path to the sitemap.xml on your site
$index_web_path = 'https://example.com';

$index_render = new PlainTextSitemapIndexRender($index_web_path);
$index_writer = new TempFileWriter();

// the file into which we will write sitemap part
// filename should contain a directive like "%d"
$part_filename = __DIR__.'/sitemap%d.xml';

// web path to pages on your site
$part_web_path = 'https://example.com';

$part_render = new PlainTextSitemapRender($part_web_path);
// separate writer for part
// it's better not to use one writer as a part writer and a index writer
// this can cause conflicts in the writer
$part_writer = new TempFileWriter();

// configure streamer
$stream = new WritingSplitIndexStream(
    $index_render,
    $part_render,
    $index_writer,
    $part_writer,
    $index_filename,
    $part_filename
);

// build sitemap.xml index file and sitemap1.xml, sitemap2.xml, sitemapN.xml with URLs
$stream->open();
$i = 0;
foreach ($builders as $url) {
    $stream->push($url);

    // not forget free memory
    if (++$i % 100 === 0) {
        gc_collect_cycles();
    }
}
$stream->close();
```

## Streams

 * `MultiStream` - allows to use multiple streams as one;
 * `WritingSplitIndexStream` - split list URLs to sitemap parts and write its with [`Writer`](#Writer) to a Sitemap
 index;
 * `WritingStream` - use [`Writer`](#Writer) for write a Sitemap;
 * `LoggerStream` - use
 [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) for log added URLs.

You can use a composition of streams.

```php
$stream = new MultiStream(
    new LoggerStream(/* $logger */),
    new WritingSplitIndexStream(
        new PlainTextSitemapIndexRender('https://example.com'),
        new PlainTextSitemapRender('https://example.com'),
        new TempFileWriter(),
        new GzipTempFileWriter(9),
         __DIR__.'/sitemap.xml',
         __DIR__.'/sitemap%d.xml.gz'
    )
);
```

Streaming to file and compress result without index.

```php
$render = new PlainTextSitemapRender('https://example.com');

$stream = new MultiStream(
    new LoggerStream(/* $logger */),
    new WritingStream($render, new GzipTempFileWriter(9), __DIR__.'/sitemap.xml.gz'),
    new WritingStream($render, new TempFileWriter(), __DIR__.'/sitemap.xml')
);
```

Streaming to file and output buffer.

```php
$render = new PlainTextSitemapRender('https://example.com');

$stream = new MultiStream(
    new LoggerStream(/* $logger */),
    new WritingStream($render, new TempFileWriter(), __DIR__.'/sitemap.xml'),
    new WritingStream($render, new OutputWriter(), '') // $filename is not used
);
```

## Writer

 * `MultiWriter` - allows to use multiple writers as one;
 * `FileWriter` - write a Sitemap to the file;
 * `TempFileWriter` - write a Sitemap to the temporary file and move in to target directory after finish writing;
 * `GzipFileWriter` - write a Sitemap to the gzip file;
 * `GzipTempFileWriter` - write a Sitemap to the temporary gzip file and move in to target directory after finish
 writing;
 * `OutputWriter` - sends a Sitemap to the output buffer. You can use it
 [in controllers](http://symfony.com/doc/current/components/http_foundation.html#streaming-a-response);
 * `CallbackWriter` - use callback for write a Sitemap;

## Render

If you install the [XMLWriter](https://www.php.net/manual/en/book.xmlwriter.php) PHP extension, you can use
`XMLWriterSitemapRender` and `XMLWriterSitemapIndexRender`. Otherwise you can use `PlainTextSitemapRender` and
`PlainTextSitemapIndexRender` who do not require any dependencies and are more economical.

## License

This bundle is under the [MIT license](http://opensource.org/licenses/MIT). See the complete license in the file:
LICENSE
