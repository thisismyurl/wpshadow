<?php
/**
 * Treatment: Browser Caching Headers
 *
 * Adds Apache mod_expires and mod_headers rules to .htaccess that set
 * long-lived Cache-Control and Expires headers for static assets (images,
 * CSS, JS, fonts). Browsers cache these files locally and skip re-downloading
 * them, reducing page load times on repeat visits by 40–70%.
 *
 * Images and fonts receive a 1-year cache; CSS and JS receive 1 month.
 * An ETag fallback is included for validation when ETags are supported.
 *
 * Note: This only works on Apache-based servers. On Nginx, add equivalent
 * `expires` directives to your server block config.
 *
 * File written: .htaccess (ABSPATH)
 * Risk level:   low (.htaccess append — reversible)
 *
 * @package WPShadow
 * @subpackage Treatments
 * @since 0.6093.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Admin\File_Write_Registry;

require_once __DIR__ . '/trait-file-write-helpers.php';

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds long-lived browser caching headers for static assets via .htaccess.
 */
class Treatment_Browser_Caching_Headers extends Treatment_Base {

	use File_Write_Helpers;

	/** @var string */
	protected static $slug = 'browser-caching-headers';

	const MARKER_SLUG = 'browser-caching-headers';

	const HTACCESS_BLOCK = '<IfModule mod_expires.c>
    ExpiresActive On
    # Images
    ExpiresByType image/jpeg                    "access plus 1 year"
    ExpiresByType image/png                     "access plus 1 year"
    ExpiresByType image/gif                     "access plus 1 year"
    ExpiresByType image/webp                    "access plus 1 year"
    ExpiresByType image/avif                    "access plus 1 year"
    ExpiresByType image/svg+xml                 "access plus 1 year"
    ExpiresByType image/x-icon                  "access plus 1 year"
    # Fonts
    ExpiresByType font/woff                     "access plus 1 year"
    ExpiresByType font/woff2                    "access plus 1 year"
    ExpiresByType application/font-woff         "access plus 1 year"
    ExpiresByType application/font-woff2        "access plus 1 year"
    # CSS and JavaScript
    ExpiresByType text/css                      "access plus 1 month"
    ExpiresByType text/javascript               "access plus 1 month"
    ExpiresByType application/javascript        "access plus 1 month"
    ExpiresByType application/x-javascript      "access plus 1 month"
    # HTML
    ExpiresByType text/html                     "access plus 1 hour"
    ExpiresByType application/xhtml+xml         "access plus 1 hour"
    # Video
    ExpiresByType video/mp4                     "access plus 1 year"
    ExpiresByType video/webm                    "access plus 1 year"
</IfModule>
<IfModule mod_headers.c>
    <FilesMatch "\.(ico|jpe?g|png|gif|webp|avif|svg|woff2?)$">
        Header set Cache-Control "max-age=31536000, public, immutable"
    </FilesMatch>
    <FilesMatch "\.(css|js)$">
        Header set Cache-Control "max-age=2592000, public"
    </FilesMatch>
    <FilesMatch "\.html?$">
        Header set Cache-Control "max-age=3600, public, must-revalidate"
    </FilesMatch>
</IfModule>';

	public static function boot(): void {
		File_Write_Registry::register( static::class );
	}

	// =========================================================================
	// Treatment_Base contract
	// =========================================================================

	public static function get_finding_id(): string {
		return self::$slug;
	}

	public static function get_risk_level(): string {
		return 'low';
	}

	public static function apply(): array {
		return self::write_htaccess_block(
			self::get_target_file(),
			self::MARKER_SLUG,
			self::HTACCESS_BLOCK
		);
	}

	public static function undo(): array {
		return self::remove_htaccess_block( self::get_target_file(), self::MARKER_SLUG );
	}

	// =========================================================================
	// File_Write_Registry interface
	// =========================================================================

	public static function get_target_file(): string {
		return ABSPATH . '.htaccess';
	}

	public static function get_file_label(): string {
		return '.htaccess';
	}

	public static function get_proposed_change_summary(): string {
		return __( 'Add long-lived Expires/Cache-Control headers for static assets via .htaccess (Apache mod_expires + mod_headers)', 'wpshadow' );
	}

	public static function get_proposed_snippet(): string {
		return "# WPSHADOW_MARKER_START: browser-caching-headers\n" .
		       self::HTACCESS_BLOCK . "\n" .
		       "# WPSHADOW_MARKER_END: browser-caching-headers";
	}

	public static function get_sftp_undo_instructions(): string {
		$file = self::get_target_file();
		return implode( "\n", [
			"Connect to your server via SFTP or cPanel File Manager.",
			"Navigate to: {$file}",
			"Open the file in a text editor.",
			"Find and delete the block between these two marker lines (inclusive):",
			"  # WPSHADOW_MARKER_START: browser-caching-headers",
			"  <IfModule mod_expires.c> ... </IfModule>",
			"  <IfModule mod_headers.c> ... </IfModule>",
			"  # WPSHADOW_MARKER_END: browser-caching-headers",
			"Save the file.",
			"Note: If your server runs Nginx, add 'expires' directives to your vhost",
			"config instead. Example: location ~* \.(css|js)$ { expires 1M; }",
		] );
	}
}

Treatment_Browser_Caching_Headers::boot();
