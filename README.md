# HEIC Support by This Is My URL

A free, non-destructive HEIC to WebP converter for WordPress. Automatically optimizes Apple device uploads with secure backups and one-click restore.

Modern iOS devices capture images in HEIC/HEIF formats that are often incompatible with web browsers. This plugin converts those images to WebP at upload time, significantly reducing file size while maintaining visual quality.

## Features

- **Automatic conversion:** New HEIC and HEIF uploads are converted to WebP the moment they hit your Media Library.
- **Bulk processing:** Convert your existing library using an AJAX-powered tool that prevents server timeouts.
- **Non-destructive workflow:** Original HEIC images are moved to `/uploads/heic-backups/` and can be restored at any time.
- **One-click restore:** Restore original HEIC files from the Media Library at any time.
- **No external API required:** All processing runs locally using server-side image libraries.

## Requirements

- WordPress 6.0+
- PHP 7.4+
- GD or Imagick with HEIC/HEIF support

## Installation

1. Upload the plugin to `/wp-content/plugins/thisismyurl-heic-support/`.
2. Activate through the WordPress Plugins screen.
3. Go to **Tools > HEIC Support**.
4. Run bulk conversion on your existing library or enable auto-conversion on upload.

## How Backup and Restore Works

On conversion, the original HEIC file is moved to `uploads/heic-backups/`. The attachment is updated to point to the new `.webp` file. Restoring moves the original back and restores all attachment metadata.

## Versioning

This plugin uses the format `1.Yddd`:
- `Y` = last digit of the year
- `ddd` = Julian day number

## Standards

- Direct access protection with ABSPATH checks.
- Nonce and capability checks for AJAX and admin actions.
- Escaping and sanitization aligned with WordPress coding standards.

---

## About This Is My URL

This plugin is built and maintained by [This Is My URL](https://thisismyurl.com/), a WordPress development and technical SEO practice with more than 25 years of experience helping organizations build practical, maintainable web systems.

Christopher Ross ([@thisismyurl](https://profiles.wordpress.org/thisismyurl/)) is a WordCamp speaker, plugin developer, and WordPress practitioner based in Fort Erie, Ontario, Canada. Member of the WordPress community since 2007.

### More Resources

- **Plugin page:** [https://thisismyurl.com/thisismyurl-heic-support/](https://thisismyurl.com/thisismyurl-heic-support/)
- **WordPress.org profile:** [profiles.wordpress.org/thisismyurl](https://profiles.wordpress.org/thisismyurl/)
- **Other plugins:** [github.com/thisismyurl](https://github.com/thisismyurl)
- **Website:** [thisismyurl.com](https://thisismyurl.com/)

## License

GPL-2.0-or-later — see [LICENSE](LICENSE) or [gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html).
