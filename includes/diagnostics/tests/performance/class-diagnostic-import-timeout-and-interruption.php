<?php
/**
 * Import Timeout and Interruption Diagnostic
 *
 * Tests whether imports may timeout or be interrupted.
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
 * Import Timeout and Interruption Diagnostic Class
 *
 * Tests server timeout and execution time limits that may interrupt imports.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Import_Timeout_And_Interruption extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-timeout-and-interruption';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Import Timeout and Interruption';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether imports may timeout or be interrupted';

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

		// Check PHP max_execution_time.
		$max_exec = ini_get( 'max_execution_time' );
		$max_exec_int = (int) $max_exec;

		if ( $max_exec_int > 0 && $max_exec_int < 300 ) {
			$issues[] = sprintf(
				/* translators: %d: execution time in seconds */
				__( 'PHP max_execution_time is low (%d seconds) - may interrupt imports', 'wpshadow' ),
				$max_exec_int
			);
		}

		// Check for set_time_limit support.
		if ( function_exists( 'set_time_limit' ) ) {
			// Function exists, good.
		} else {
			$issues[] = __( 'set_time_limit() function disabled - cannot extend timeout during imports', 'wpshadow' );
		}

		// Check PHP memory limit.
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );

		if ( $memory_bytes > 0 && $memory_bytes < 134217728 ) { // 128MB
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'PHP memory_limit is low (%s) - may run out during large imports', 'wpshadow' ),
				$memory_limit
			);
		}

		// Check upload file size limits.
		$upload_max = ini_get( 'upload_max_filesize' );
		$upload_bytes = wp_convert_hr_to_bytes( $upload_max );
		$post_max = ini_get( 'post_max_size' );
		$post_bytes = wp_convert_hr_to_bytes( $post_max );

		if ( $upload_bytes < 10485760 ) { // 10MB
			$issues[] = sprintf(
				/* translators: %s: current upload limit */
				__( 'upload_max_filesize is small (%s) - may block media imports', 'wpshadow' ),
				$upload_max
			);
		}

		if ( $post_bytes < $upload_bytes ) {
			$issues[] = sprintf(
				/* translators: %s: current post limit */
				__( 'post_max_size (%s) is smaller than upload_max_filesize - may block large uploads', 'wpshadow' ),
				$post_max
			);
		}

		// Check if async processing is available (for large imports).
		$has_background_processing = function_exists( 'wp_get_auto_updater' ) || \get_option( 'background_processing_enabled' );
		if ( ! $has_background_processing ) {
			// Background processing not available.
		}

		// Check for database connection timeout.
		global $wpdb;
		$db_timeout = $wpdb->get_var( "SELECT @@connect_timeout" );
		if ( $db_timeout && $db_timeout < 10 ) {
			$issues[] = sprintf(
				/* translators: %d: timeout in seconds */
				__( 'Database connect_timeout is low (%d seconds)', 'wpshadow' ),
				$db_timeout
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/import-timeout-and-interruption',
			);
		}

		return null;
	}
}
