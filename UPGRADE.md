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
  PlainTextSitemapRender::sitemap(string $path, ?\DateTimeInterface $last_modify = null)
  ```

  After:

  ```php
  PlainTextSitemapRender::sitemap(Sitemap $sitemap)
  ```

* The `$host` argument in `RenderIndexFileStream::__construct()` was removed.
* The `$web_path` argument in `PlainTextSitemapIndexRender::__construct()` was added.

  Before:

  ```php
  $web_path = 'https://example.com/';
  $index_render = new PlainTextSitemapIndexRender();
  $index_stream = new RenderFileStream($index_render, $stream, $web_path, $filename_index);
  ```

  After:

  ```php
  $web_path = 'https://example.com'; // No slash in end of path!
  $index_render = new PlainTextSitemapIndexRender($web_path);
  $index_stream = new RenderFileStream($index_render, $stream, $filename_index);
  ```

* The `$web_path` argument in `PlainTextSitemapRender::__construct()` was added.

  Before:

  ```php
  $render = new PlainTextSitemapRender();
  $render->url(new Url('https://example.com'));
  $render->url(new Url('https://example.com/about'));
  ```

  After:

  ```php
  $web_path = 'https://example.com'; // No slash in end of path!
  $render = new PlainTextSitemapRender($web_path);
  $render->url(new Url('/'));
  $render->url(new Url('/about'));
  ```

* The `$priority` in `URL` class was changed from `string` to `int`.

  Before:

  ```php
  new Url('/contacts.html', new \DateTimeImmutable('-1 month'), ChangeFrequency::MONTHLY, '0.7');
  ```

  After:

  ```php
  new Url('/contacts.html', new \DateTimeImmutable('-1 month'), ChangeFrequency::MONTHLY, 7);
  ```

* The `OutputStream` was removed. Use `WritingStream` instead.

  Before:

  ```php
  $stream = new OutputStream($render);
  ```

  After:

  ```php
  $stream = new WritingStream($render, new OutputWriter(), '');
  ```

* The `CallbackStream` was removed. Use `WritingStream` instead.

  Before:

  ```php
  $stream = new CallbackStream($render, $callback);
  ```

  After:

  ```php
  $stream = new WritingStream($render, new CallbackWriter($callback), '');
  ```

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
  $index_web_path = 'https://example.com';
  $part_filename = __DIR__.'/sitemap%d.xml';
  $part_web_path = 'https://example.com';

  $index_render = new PlainTextSitemapIndexRender($index_web_path);
  $index_writer = new TempFileWriter();
  $part_render = new PlainTextSitemapRender($part_web_path);
  $part_writer = new TempFileWriter();

  $stream = new WritingSplitIndexStream(
      $index_render,
      $part_render,
      $index_writer,
      $part_writer,
      $index_filename,
      $part_filename
  );
  ```

* The `CompressionLevelException` was removed.
* The `FileAccessException` was removed.
