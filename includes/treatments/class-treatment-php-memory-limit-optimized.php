<?php
/**
 * Treatment: PHP Memory Limit Optimized
 *
 * Writes `define( 'WP_MEMORY_LIMIT', '256M' )` to wp-config.php so WordPress
 * requests are allowed to use up to 256 MB of memory. A separate constant
 * `WP_MAX_MEMORY_LIMIT` is set to 512M for admin/WP-Cron processes. Both use
 * the marker-block approach and are fully reversible.
 *
 * Note: `WP_MEMORY_LIMIT` is a WordPress-level cap; PHP's `memory_limit` in
 * php.ini must be >= this value for the constant to have any effect. If php.ini
 * is already set lower, the effective limit will be the lower of the two.
 *
 * File written: wp-config.php
 * Risk level:   medium (file write, non-destructive)
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
 * Sets WP_MEMORY_LIMIT to 256M and WP_MAX_MEMORY_LIMIT to 512M in wp-config.php.
 */
class Treatment_Php_Memory_Limit_Optimized extends Treatment_Base {

	use File_Write_Helpers;

	/** @var string */
	protected static $slug = 'php-memory-limit-optimized';

	const MARKER_SLUG = 'php-memory-limit-optimized';

	const DEFINE_BLOCK = "define( 'WP_MEMORY_LIMIT', '256M' );     // WPShadow: front-end PHP memory cap\n" .
	                     "define( 'WP_MAX_MEMORY_LIMIT', '512M' ); // WPShadow: admin/WP-Cron PHP memory cap";

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
		return 'medium';
	}

	public static function apply(): array {
		return self::write_wp_config_define(
			self::get_target_file(),
			self::MARKER_SLUG,
			self::DEFINE_BLOCK
		);
	}

	public static function undo(): array {
		return self::remove_wp_config_block( self::get_target_file(), self::MARKER_SLUG );
	}

	// =========================================================================
	// File_Write_Registry interface
	// =========================================================================

	public static function get_target_file(): string {
		return ABSPATH . 'wp-config.php';
	}

	public static function get_file_label(): string {
		return 'wp-config.php';
	}

	public static function get_proposed_change_summary(): string {
		return __( 'Set WP_MEMORY_LIMIT to 256M and WP_MAX_MEMORY_LIMIT to 512M in wp-config.php', 'wpshadow' );
	}

	public static function get_proposed_snippet(): string {
		return "// WPSHADOW_MARKER_START: php-memory-limit-optimized\n" .
		       self::DEFINE_BLOCK . "\n" .
		       "// WPSHADOW_MARKER_END: php-memory-limit-optimized";
	}

	public static function get_sftp_undo_instructions(): string {
		$file = self::get_target_file();
		return implode( "\n", [
			"Connect to your server via SFTP or cPanel File Manager.",
			"Navigate to: {$file}",
			"Open the file in a text editor.",
			"Find and delete the block between these two marker lines (inclusive):",
			"  // WPSHADOW_MARKER_START: php-memory-limit-optimized",
			"  define( 'WP_MEMORY_LIMIT', '256M' );",
			"  define( 'WP_MAX_MEMORY_LIMIT', '512M' );",
			"  // WPSHADOW_MARKER_END: php-memory-limit-optimized",
			"Save the file.",
			"WordPress will revert to its built-in memory defaults.",
		] );
	}
}

Treatment_Php_Memory_Limit_Optimized::boot();
