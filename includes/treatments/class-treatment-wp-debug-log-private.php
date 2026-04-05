<?php
/**
 * Treatment: WP Debug Log Private
 *
 * Adds an Apache .htaccess rule to deny public access to WordPress's
 * debug.log file. When WP_DEBUG_LOG is enabled, WordPress writes PHP errors
 * to wp-content/debug.log. Without protection, this file is publicly
 * accessible via browser and may expose sensitive information including
 * file paths, database errors, and potentially credentials.
 *
 * The rule denies HTTP access for Apache servers. For Nginx, a different
 * config block is needed — the treatment notes this in its preview.
 *
 * File written: .htaccess (in ABSPATH)
 * Risk level:   high (file write)
 *
 * @package WPShadow
 * @subpackage Treatments
 * @since 0.6093.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Admin\File_Write_Registry;

// Load the shared file-write helpers trait.
require_once __DIR__ . '/trait-file-write-helpers.php';

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Blocks public browser access to the WordPress debug.log file.
 */
class Treatment_Wp_Debug_Log_Private extends Treatment_Base {

	use File_Write_Helpers;

	/** @var string */
	protected static $slug = 'wp-debug-log-private';

	const MARKER_SLUG = 'wp-debug-log-private';

	/**
	 * The .htaccess block that blocks access to debug.log.
	 * Compatible with Apache 2.2 (.htaccess Order/Deny) and 2.4 (<RequireAll>).
	 */
	const HTACCESS_BLOCK = '<Files "debug.log">
    # Apache 2.2
    <IfModule !mod_authz_core.c>
        Order allow,deny
        Deny from all
    </IfModule>
    # Apache 2.4
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
</Files>';

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
		return 'high';
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
		return __( 'Block public access to debug.log via .htaccess (Apache)', 'wpshadow' );
	}

	public static function get_proposed_snippet(): string {
		return "# WPSHADOW_MARKER_START: wp-debug-log-private\n" .
		       self::HTACCESS_BLOCK . "\n" .
		       "# WPSHADOW_MARKER_END: wp-debug-log-private";
	}

	public static function get_sftp_undo_instructions(): string {
		$file = self::get_target_file();
		return implode( "\n", [
			"Connect to your server via SFTP or cPanel File Manager.",
			"Navigate to: {$file}",
			"Open the file in a text editor.",
			"Find and delete the block that looks like:",
			"  # WPSHADOW_MARKER_START: wp-debug-log-private",
			"  <Files \"debug.log\">",
			"    ... (Apache deny rules)",
			"  </Files>",
			"  # WPSHADOW_MARKER_END: wp-debug-log-private",
			"Save the file.",
			"Reload your WordPress site to confirm it works.",
			"Note: If your server runs Nginx (not Apache), this rule has no effect.",
			"For Nginx, add 'location ~* /debug\\.log { deny all; }' in your server block instead.",
		] );
	}
}

Treatment_Wp_Debug_Log_Private::boot();
