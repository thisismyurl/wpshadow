<?php
/**
 * Treatment: Compression Enabled
 *
 * Adds Apache mod_deflate and mod_brotli .htaccess rules to enable HTTP
 * compression for HTML, CSS, JavaScript, XML, and SVG responses. Compression
 * typically reduces transfer sizes by 60–80%. Both Apache 2.2 and 2.4
 * conditional module directives are used so the rules degrade gracefully on
 * servers where the compression module is absent.
 *
 * Note: This only works on Apache-based servers (most cPanel/shared hosts).
 * On Nginx, compression is configured in nginx.conf; this treatment will have
 * no effect and the diagnostic will remain active.
 *
 * File written: .htaccess (ABSPATH)
 * Risk level:   low (.htaccess append — reversible)
 *
 * @package WPShadow
 * @subpackage Treatments
 * @since 0.6095
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
 * Adds gzip/Brotli compression rules to .htaccess.
 */
class Treatment_Compression_Enabled extends Treatment_Base {

	use File_Write_Helpers;

	/** @var string */
	protected static $slug = 'compression-enabled';

	const MARKER_SLUG = 'compression-enabled';

	const HTACCESS_BLOCK = '<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/atom+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/json
    AddOutputFilterByType DEFLATE image/svg+xml
    # Handle proxied requests
    <IfModule mod_headers.c>
        Header append Vary Accept-Encoding
    </IfModule>
</IfModule>
<IfModule mod_brotli.c>
    AddOutputFilterByType BROTLI_COMPRESS text/html text/plain text/xml
    AddOutputFilterByType BROTLI_COMPRESS text/css application/javascript
    AddOutputFilterByType BROTLI_COMPRESS application/json image/svg+xml
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
		return __( 'Enable gzip and Brotli HTTP compression via .htaccess (Apache mod_deflate / mod_brotli)', 'wpshadow' );
	}

	public static function get_proposed_snippet(): string {
		return "# WPSHADOW_MARKER_START: compression-enabled\n" .
		       self::HTACCESS_BLOCK . "\n" .
		       "# WPSHADOW_MARKER_END: compression-enabled";
	}

	public static function get_sftp_undo_instructions(): string {
		$file = self::get_target_file();
		return implode( "\n", [
			"Connect to your server via SFTP or cPanel File Manager.",
			"Navigate to: {$file}",
			"Open the file in a text editor.",
			"Find and delete the block between these two marker lines (inclusive):",
			"  # WPSHADOW_MARKER_START: compression-enabled",
			"  <IfModule mod_deflate.c> ... </IfModule>",
			"  <IfModule mod_brotli.c> ... </IfModule>",
			"  # WPSHADOW_MARKER_END: compression-enabled",
			"Save the file.",
			"Note: If your server runs Nginx, these rules have no effect.",
			"For Nginx, compression is configured in your nginx.conf/vhost config.",
		] );
	}
}

Treatment_Compression_Enabled::boot();
