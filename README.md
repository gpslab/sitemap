[![Latest Stable Version](https://img.shields.io/packagist/v/gpslab/sitemap.svg?maxAge=3600&label=stable)](https://packagist.org/packages/gpslab/sitemap)
[![Total Downloads](https://img.shields.io/packagist/dt/gpslab/sitemap.svg?maxAge=3600)](https://packagist.org/packages/gpslab/sitemap)
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

Create a service that will return a links to pages of your site.

```php
use GpsLab\Component\Sitemap\Builder\Url\UrlBuilder;
use GpsLab\Component\Sitemap\Url\Url;

class MySiteUrlBuilder implements UrlBuilder
{
    private $urls;

    public function __construct()
    {
        // add URLs on your site
        $this->urls = new \ArrayIterator([
            new Url(
                'https://example.com/', // loc
                new \DateTimeImmutable('-10 minutes'), // lastmod
                Url::CHANGE_FREQ_ALWAYS, // changefreq
                '1.0' // priority
            ),
            new Url(
                'https://example.com/contacts.html',
                new \DateTimeImmutable('-1 month'),
                Url::CHANGE_FREQ_MONTHLY,
                '0.7'
            ),
            new Url(
                'https://example.com/about.html',
                new \DateTimeImmutable('-2 month'),
                Url::CHANGE_FREQ_MONTHLY,
                '0.7'
            ),
        ]);
    }

    public function getName()
    {
        return 'My Site';
    }

    public function count()
    {
        return count($this->urls);
    }

    public function getIterator()
    {
        return $this->urls;
    }
}
```

It was a simple build. We add a builder more complicated.

```php
use GpsLab\Component\Sitemap\Builder\Url\UrlBuilder;
use GpsLab\Component\Sitemap\Url\Url;

class ArticlesUrlBuilder implements UrlBuilder
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getName()
    {
        return 'Articles on my site';
    }

    public function count()
    {
        $total = $this->pdo->query('SELECT COUNT(*) FROM article')->fetchColumn(); 
        $total++; // +1 for section
 
        return $total;
    }

    public function getIterator()
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
            Url::CHANGE_FREQ_DAILY,
            '0.9'
        );
    }
}
```

We take one of the exists builders and configure it.

```php
// collect a collection of builders
$collection = new UrlBuilderCollection([
    new MySiteUrlBuilder(),
    new ArticlesUrlBuilder(/* $pdo */),
]);

// the file into which we will write our sitemap
$filename = __DIR__.'/sitemap.xml';

// configure streamer
$render = new PlainTextSitemapRender();
$stream = new RenderFileStream($render, $filename);

// configure sitemap builder
$builder = new SilentSitemapBuilder($collection, $stream);

// build sitemap.xml
$total_urls = $builder->build();
```

## Sitemap index

You can create [Sitemap index](https://www.sitemaps.org/protocol.html#index) to group multiple sitemap files.

```php
// collect a collection of builders
$collection = new UrlBuilderCollection([
    new MySiteUrlBuilder(),
    new ArticlesUrlBuilder(/* $pdo */),
]);

// the file into which we will write our sitemap
$filename = __DIR__.'/sitemap.xml';

// configure streamer
$render = new PlainTextSitemapRender();
$stream = new RenderFileStream($render, $filename)

// configure index streamer
$index_render = new PlainTextSitemapIndexRender();
$index_stream = new RenderFileStream($index_render, $stream, 'https://example.com/', $filename);

// configure sitemap builder
$builder = new SilentSitemapBuilder($collection, $index_stream);

// build sitemap.xml index file and sitemap1.xml, sitemap2.xml, sitemapN.xml with URLs
$total_urls = $builder->build();
```

## Symfony sitemap builder

If you use Symfony, you can use `SymfonySitemapBuilder` in console.

```php
class BuildSitemapCommand extends Command
{
    private $builder;

    public function __construct(SymfonySitemapBuilder $builder)
    {
        $this->builder = $builder;
    }


    protected function configure()
    {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // build sitemap.xml
        $total_urls = $this->builder->build($io);

        $io->success(sprintf('Build "%d" urls.', $total_urls));
    }
}
```

## Streams

 * `LoggerStream` - use [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
 for log added URLs
 * `MultiStream` - allows to use multiple streams as one
 * `OutputStream` - sends a Sitemap to the output buffer. You can use it
[in controllers](http://symfony.com/doc/current/components/http_foundation.html#streaming-a-response).
 * `RenderFileStream` - writes a Sitemap to file
 * `RenderIndexFileStream` - writes a Sitemap index to file
 * `RenderGzipFileStream` - writes a Sitemap to Gzip file
 * `RenderBzip2FileStream` - writes a Sitemap to Bzip2 file
 * `CompressFileStream` - use `gpslab/compressor` for compress `sitemap.xml`

You can use a composition from streams.

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

Streaming to file and compress result without index

```php
$stream = new MultiStream(
    new LoggerStream(/* $logger */),
    new CompressFileStream(
        new RenderFileStream(
            new PlainTextSitemapRender(),
            __DIR__.'/sitemap.xml'
        ),
        new GzipCompressor(),
        __DIR__.'/sitemap.xml.gz'
    )
);
```

Streaming to file and output buffer

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

## License

This bundle is under the [MIT license](http://opensource.org/licenses/MIT). See the complete license in the file: LICENSE
