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
       'https://example.com/', // loc
       new \DateTimeImmutable('-10 minutes'), // lastmod
       ChangeFreq::ALWAYS, // changefreq
       '1.0' // priority
   ),
   new Url(
       'https://example.com/contacts.html',
       new \DateTimeImmutable('-1 month'),
       ChangeFreq::MONTHLY,
       '0.7'
   ),
   new Url(
       'https://example.com/about.html',
       new \DateTimeImmutable('-2 month'),
       ChangeFreq::MONTHLY,
       '0.7'
   ),
];

// the file into which we will write our sitemap
$filename = __DIR__.'/sitemap.xml';

// configure streamer
$render = new PlainTextSitemapRender();
$stream = new RenderFileStream($render, $filename);

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
              'https://example.com/', // loc
              new \DateTimeImmutable('-10 minutes'), // lastmod
              ChangeFreq::ALWAYS, // changefreq
              '1.0' // priority
          ),
          new Url(
              'https://example.com/contacts.html',
              new \DateTimeImmutable('-1 month'),
              ChangeFreq::MONTHLY,
              '0.7'
          ),
          new Url(
              'https://example.com/about.html',
              new \DateTimeImmutable('-2 month'),
              ChangeFreq::MONTHLY,
              '0.7'
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
                sprintf('https://example.com/article/%d', $row['id']),
                $update_at
            );
        }

        // link to section
        yield new Url(
            'https://example.com/article/',
            $section_update_at ?: new \DateTimeImmutable('-1 day'),
            ChangeFreq::DAILY,
            '0.9'
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

// configure streamer
$render = new PlainTextSitemapRender();
$stream = new RenderFileStream($render, $filename);

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
$filename_index = __DIR__.'/sitemap.xml';

// the file into which we will write sitemap part
// you must use the temporary directory if you don't want to overwrite the existing index file!!!
// the sitemap part file will be automatically moved to the directive with the sitemap index on close stream
$filename_part = sys_get_temp_dir().'/sitemap.xml';

// configure streamer
$render = new PlainTextSitemapRender();
$stream = new RenderFileStream($render, $filename_part)

// web path to the sitemap.xml on your site
$web_path = 'https://example.com/';

// configure index streamer
$index_render = new PlainTextSitemapIndexRender($web_path);
$index_stream = new RenderFileStream($index_render, $stream, $filename_index);

// build sitemap.xml index file and sitemap1.xml, sitemap2.xml, sitemapN.xml with URLs
$index_stream->open();
$i = 0;
foreach ($builders as $url) {
    $index_stream->push($url);

    // not forget free memory
    if (++$i % 100 === 0) {
        gc_collect_cycles();
    }
}
$index_stream->close();
```

## Streams

 * `MultiStream` - allows to use multiple streams as one;
 * `RenderFileStream` - writes a Sitemap to the file;
 * `RenderGzipFileStream` - writes a Sitemap to the gzip file;
 * `RenderIndexFileStream` - writes a Sitemap index to the file;
 * `OutputStream` - sends a Sitemap to the output buffer. You can use it
[in controllers](http://symfony.com/doc/current/components/http_foundation.html#streaming-a-response);
 * `CallbackStream` - use callback for streaming a Sitemap;
 * `LoggerStream` - use [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
 for log added URLs.

You can use a composition of streams.

```php
$stream = new MultiStream(
    new LoggerStream(/* $logger */),
    new RenderIndexFileStream(
        new PlainTextSitemapIndexRender(),
        new RenderGzipFileStream(
            new PlainTextSitemapRender(),
            __DIR__.'/sitemap.xml.gz'
        ),
        'https://example.com/',
         __DIR__.'/sitemap.xml',
    )
);
```

Streaming to file and compress result without index.

```php
$stream = new MultiStream(
    new LoggerStream(/* $logger */),
    new RenderGzipFileStream(
        new PlainTextSitemapRender(),
        __DIR__.'/sitemap.xml.gz'
    ),
);
```

Streaming to file and output buffer.

```php
$stream = new MultiStream(
    new LoggerStream(/* $logger */),
    new RenderFileStream(
        new PlainTextSitemapRender(),
        __DIR__.'/sitemap.xml'
    ),
    new OutputStream(
        new PlainTextSitemapRender()
    )
);
```

## Render

If you install the [XMLWriter](https://www.php.net/manual/en/book.xmlwriter.php) PHP extension, you can use
`XMLWriterSitemapRender` and `XMLWriterSitemapIndexRender`. Otherwise you can use `PlainTextSitemapRender` and
`PlainTextSitemapIndexRender` who do not require any dependencies and are more economical.

## License

This bundle is under the [MIT license](http://opensource.org/licenses/MIT). See the complete license in the file: LICENSE
