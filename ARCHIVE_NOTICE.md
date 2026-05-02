# Archived — 2026-05-02

This repository is archived. Read-only.

## Why

The plugin shipped with an empty conversion handler — `handle_heic_upload()` was a stub that returned the upload unchanged, while the plugin description claimed automatic HEIC → WebP conversion. That mismatch between the marketing surface and the code is not something I'm willing to keep public under the This Is My URL brand.

A clean rebuild — using Imagick with a graceful fallback for hosts that don't ship HEIC support — may happen in the future. If it does, it'll ship as a fresh repo and a fresh `readme.txt`, not as a quiet 1.0 here.

## What to do instead

- For HEIC images on iPhone, set the camera capture format to "Most Compatible" (Settings → Camera → Formats) so iOS exports JPEG directly.
- For server-side HEIC handling, [Imagick](https://www.php.net/manual/en/book.imagick.php) with libheif support is the right primitive.
- For broader media-library work, see [thisismyurl-image-support](https://github.com/thisismyurl/thisismyurl-image-support) and [thisismyurl-webp-support](https://github.com/thisismyurl/thisismyurl-webp-support).

— Christopher Ross / [thisismyurl.com](https://thisismyurl.com/)
