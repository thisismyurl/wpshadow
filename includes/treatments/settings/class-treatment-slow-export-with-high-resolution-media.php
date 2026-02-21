<?php
/**
 * Slow Export with High-Resolution Media
 *
 * Detects performance degradation when exporting content with large media files.
 *
 * @package    WPShadow
 * @subpackage Treatments\Export
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Slow_Export_With_High_Resolution_Media Class
 *
 * Tests performance degradation when exporting high-resolution media.
 * Monitors export speed, media processing, and file handling efficiency.
 *
 * @since 1.6030.2148
 */
class Treatment_Slow_Export_With_High_Resolution_Media extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'slow-export-with-high-resolution-media';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Export Performance with Media';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects performance issues when exporting large media files';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the treatment check.
	 *
	 * Tests for media-related export performance issues.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Slow_Export_With_High_Resolution_Media' );
	}

	/**
	 * Get media library statistics.
	 *
	 * @since  1.6030.2148
	 * @return array {
	 *     Media statistics.
	 *
	 *     @type int $large_file_count Number of files > 10MB.
	 *     @type int $total_size_mb Total media library size in MB.
	 * }
	 */
	private static function get_media_statistics() {
		global $wpdb;

		$uploads = wp_upload_dir();
		$upload_dir = $uploads['basedir'];

		$large_file_count = 0;
		$total_size_bytes = 0;

		// Query attachments
		$attachments = $wpdb->get_results(
			"SELECT meta_value FROM {$wpdb->postmeta} 
			WHERE meta_key = '_wp_attached_file' 
			LIMIT 1000"
		);

		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$file_path = $upload_dir . '/' . $attachment->meta_value;

				if ( file_exists( $file_path ) ) {
					$file_size = filesize( $file_path );
					$total_size_bytes += $file_size;

					// 10MB threshold for "large"
					if ( $file_size > 10485760 ) {
						$large_file_count++;
					}
				}
			}
		}

		return array(
			'large_file_count' => $large_file_count,
			'total_size_mb'    => round( $total_size_bytes / 1048576, 2 ),
		);
	}

	/**
	 * Check if export supports media reference only mode.
	 *
	 * @since  1.6030.2148
	 * @return bool True if media reference-only export available.
	 */
	private static function supports_media_reference_only() {
		// Check for export filter that skips media files
		if ( has_filter( 'wxr_export_skip_postmeta' ) ) {
			return true;
		}

		// Check for plugin that supports media-only references
		$reference_plugins = array(
			'wp-migrate-db/wp-migrate-db.php',
			'all-in-one-migration/all-in-one-migration.php',
		);

		foreach ( $reference_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if thumbnails are generated during export.
	 *
	 * @since  1.6030.2148
	 * @return bool True if thumbnail generation occurs during export.
	 */
	private static function has_heavy_thumbnail_generation() {
		// Check if Regenerate Thumbnails is active
		if ( is_plugin_active( 'regenerate-thumbnails/regenerate-thumbnails.php' ) ) {
			return true;
		}

		// Check for thumbnail regeneration in export hooks
		if ( has_filter( 'wp_generate_attachment_metadata' ) ) {
			// Count how many hooks are attached
			global $wp_filter;
			if ( isset( $wp_filter['wp_generate_attachment_metadata'] ) ) {
				$count = count( $wp_filter['wp_generate_attachment_metadata'] );
				return $count > 2; // More than core default
			}
		}

		return false;
	}

	/**
	 * Check disk I/O performance.
	 *
	 * @since  1.6030.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_disk_io_performance() {
		$upload_dir = wp_upload_dir();
		$basedir    = $upload_dir['basedir'];

		// Try to write/read test file to check performance
		$test_file = $basedir . '/.wpshadow-export-test-' . time() . '.tmp';

		$start_time = microtime( true );
		file_put_contents( $test_file, 'test' );
		$write_time = microtime( true ) - $start_time;

		$start_time = microtime( true );
		$content    = file_get_contents( $test_file );
		$read_time  = microtime( true ) - $start_time;

		@unlink( $test_file );

		// If write takes more than 10ms or read more than 5ms, consider slow
		if ( $write_time > 0.01 || $read_time > 0.005 ) {
			return sprintf(
				/* translators: %f: write time in ms, %f: read time in ms */
				__( 'Slow disk I/O: write %.2fms, read %.2fms (may impact export)', 'wpshadow' ),
				$write_time * 1000,
				$read_time * 1000
			);
		}

		return null;
	}
}
