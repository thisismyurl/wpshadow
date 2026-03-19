<?php
/**
 * Memory Exhaustion During Import Diagnostic
 *
 * Tests whether memory limits may be exceeded during large imports.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Memory Exhaustion During Import Diagnostic Class
 *
 * Tests whether available memory is sufficient for large content imports.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Memory_Exhaustion_During_Import extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'memory-exhaustion-during-import';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Memory Exhaustion During Import';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether memory limits may be exceeded during large imports';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check current memory limit.
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );

		if ( $memory_bytes > 0 && $memory_bytes < 67108864 ) { // 64MB
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'Low memory limit configured (%s)', 'wpshadow' ),
				$memory_limit
			);
		}

		// Check current memory usage.
		if ( function_exists( 'memory_get_usage' ) ) {
			$current_usage = memory_get_usage( true );
			$remaining = $memory_bytes - $current_usage;

			// If less than 25% remaining, issue warning.
			if ( $memory_bytes > 0 && $remaining < ( $memory_bytes * 0.25 ) ) {
				$issues[] = sprintf(
					/* translators: %s: remaining memory */
					__( 'Only %s of memory remaining (%.1f%% used)', 'wpshadow' ),
					size_format( $remaining ),
					( $current_usage / $memory_bytes ) * 100
				);
			}
		}

		// Check for memory monitoring plugins.
		$has_memory_plugin = is_plugin_active( 'memory-monitor/memory-monitor.php' );
		if ( ! $has_memory_plugin ) {
			// No memory monitoring plugin.
		}

		// Check WP_DEBUG_LOG for memory warnings.
		$debug_log_file = WP_CONTENT_DIR . '/debug.log';
		if ( file_exists( $debug_log_file ) ) {
			$file_size   = filesize( $debug_log_file );
			$start_byte  = ( false !== $file_size && $file_size > 1000 ) ? $file_size - 1000 : 0;
			$log_content = file_get_contents( $debug_log_file, false, null, $start_byte, 1000 );
			if ( is_string( $log_content ) && stripos( $log_content, 'memory' ) !== false && stripos( $log_content, 'allowed' ) !== false ) {
				$issues[] = __( 'Memory limit warnings found in debug.log', 'wpshadow' );
			}
		}

		// Check for memory leaks in large queries.
		global $wpdb;
		$post_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts}" );

		if ( $post_count > 10000 && $memory_bytes < 134217728 ) { // > 10k posts and < 128MB
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( 'Large number of posts (%d) with limited memory (%s) may cause exhaustion', 'wpshadow' ),
				$post_count,
				$memory_limit
			);
		}

		// Return finding if any issues detected.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/memory-exhaustion-during-import',
			);
		}

		return null;
	}
}
