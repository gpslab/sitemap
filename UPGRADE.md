# Upgrade from 1.0 to 2.0

The `SilentSitemapBuilder` was removed.
The `SymfonySitemapBuilder` was removed.
The `CompressFileStream` was removed.
The `RenderBzip2FileStream` was removed.
The `Stream` not extends `Countable` interface.
The `UrlBuilder` not extends `Countable` interface and not require `getName` method.
The `UrlBuilderCollection` changed to `MultiUrlBuilder`.
The `CompressionLevelException` changed to final.
The `FileAccessException` changed to final.
The `LinksOverflowException` changed to final.
The `OverflowException` changed to abstract.
The `SizeOverflowException` changed to final.
The `StreamStateException` changed to final.
The `$compression_level` in `RenderGzipFileStream` can be only integer.