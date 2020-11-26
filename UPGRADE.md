# Upgrade from 1.1 to 2.0

* The `SilentSitemapBuilder` was removed.
* The `SymfonySitemapBuilder` was removed.
* The `CompressFileStream` was removed.
* The `RenderBzip2FileStream` was removed.
* The `Stream` not extends `Countable` interface.
* The `UrlBuilder` not extends `Countable` interface and not require `getName` method.
* The `UrlBuilderCollection` changed to `MultiUrlBuilder`.
* The `CompressionLevelException` changed to final.
* The `FileAccessException` changed to final.
* The `LinksOverflowException` changed to final.
* The `OverflowException` changed to abstract.
* The `SizeOverflowException` changed to final.
* The `StreamStateException` changed to final.
* The `$compression_level` in `RenderGzipFileStream` can be only integer.
* Move `CHANGE_FREQ_*` constants from `URL` class to new `ChangeFrequency` class.
* Mark `STATE_*` constants in `StreamState` class as private.
* The `Url::getLoc()` was renamed to `Url::getLocation()` method.
* The `Url::getLastMod()` was renamed to `Url::getLastModify()` method.
* The `Url::getChangeFreq()` was renamed to `Url::getChangeFrequency()` method.
* The arguments of `PlainTextSitemapRender::sitemap()` was changed.

  Before:

  ```php
  $render = PlainTextSitemapRender::sitemap(string $path, ?\DateTimeInterface $last_modify = null)
  ```

  After:

  ```php
  $render = PlainTextSitemapRender::sitemap(Sitemap $sitemap)
  ```

* The `$host` argument in `RenderIndexFileStream::__construct()` was removed.
* The `$web_path` argument in `PlainTextSitemapIndexRender::__construct()` was removed.

  Before:

  ```php
  $web_path = 'https://example.com/';
  $index_render = new PlainTextSitemapIndexRender();
  $index_stream = new RenderFileStream($index_render, $stream, $web_path, $filename_index);
  ```

  After:

  ```php
  $index_render = new PlainTextSitemapIndexRender();
  $index_stream = new RenderFileStream($index_render, $stream, $filename_index);
  ```

* The `CallbackStream` was removed.
* The `RenderGzipFileStream` was removed. Use `WritingStream` instead.

  Before:

  ```php
  $stream = new RenderGzipFileStream($render, $filename, $compression_level);
  ```

  After:

  ```php
  $stream = new WritingStream($render, new GzipTempFileWriter($compression_level), $filename);
  ```

* The `RenderFileStream` was removed. Use `WritingStream` instead.

  Before:

  ```php
  $stream = new RenderFileStream($render, $filename);
  ```

  After:

  ```php
  $stream = new WritingStream($render, new TempFileWriter(), $filename);
  ```

* The `FileStream` was removed.
* The `RenderIndexFileStream` was removed. Use `WritingSplitIndexStream` instead.

  Before:

  ```php
  $web_path = 'https://example.com';
  $filename_index = __DIR__.'/sitemap.xml';
  $filename_part = sys_get_temp_dir().'/sitemap.xml';

  $render = new PlainTextSitemapRender();
  $stream = new RenderFileStream($render, $filename_part)
  $index_render = new PlainTextSitemapIndexRender();

  $index_stream = new RenderIndexFileStream($index_render, $stream, $web_path, $filename_index);
  ```

  After:

  ```php
  $index_filename = __DIR__.'/sitemap.xml';
  $part_filename = __DIR__.'/sitemap%d.xml';
  $part_web_path = 'https://example.com/sitemap%d.xml';

  $index_render = new PlainTextSitemapIndexRender();
  $index_writer = new TempFileWriter();
  $part_render = new PlainTextSitemapRender();
  $part_writer = new TempFileWriter();

  $stream = new WritingSplitIndexStream(
      $index_render,
      $part_render,
      $index_writer,
      $part_writer,
      $index_filename,
      $part_filename,
      $part_web_path
  );
  ```

* The `CompressionLevelException` was removed.
* The `FileAccessException` was removed.
* The `Stream::LINKS_LIMIT` constants was removed. Use `Limiter::LINKS_LIMIT` instead.
* The `Stream::BYTE_LIMIT` constants was removed. Use `Limiter::BYTE_LIMIT` instead.
* The return value of `Url::getLocation()` was changed to a `Location` object.
* The return value of `Url::getChangeFrequency()` was changed to a `ChangeFrequency` object.
* The `Url` changed to final.
* The `Url::__construct` require objects as arguments.

  Before:

  ```php
  $url = new Url('/contacts.html', new \DateTimeImmutable('-1 month'), ChangeFrequency::MONTHLY, '0.7');
  ```

  After:

  ```php
  
  $url = Url::create('https://example.com/contacts.html', new \DateTimeImmutable('-1 month'), ChangeFrequency::MONTHLY, '0.7');
  ```

  Or

  ```php
  
  $url = new Url(
      new Location('https://example.com/contacts.html'),
      new \DateTimeImmutable('-1 month'),
      ChangeFrequency::monthly(),
      Priority::create(7)
  );
  ```

* The `SmartUrl` was removed.

  Before:

  ```php
  $url = new SmartUrl('/article/123');
  ```

  After:

  ```php
  $url = Url::createSmart('https://example.com/article/123');
  ```

* Use absolute URL in `Url` class.

  Before:

  ```php
  $url = Url::create('/contacts.html');
  ```

  After:

  ```php
  $url = Url::create('https://example.com/contacts.html');
  ```

* Allow use `int` and `float` as `$priority` in `URL` class.

  Before:

  ```php
  $url = Url::create('/contacts.html', new \DateTimeImmutable('-1 month'), ChangeFrequency::MONTHLY, '0.7');
  ```

  After:

  ```php
  $url = Url::create('https://example.com/contacts.html', new \DateTimeImmutable('-1 month'), ChangeFrequency::monthly(), 7);
  $url = Url::create('https://example.com/contacts.html', new \DateTimeImmutable('-1 month'), ChangeFrequency::monthly(), .7);
  ```
