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
