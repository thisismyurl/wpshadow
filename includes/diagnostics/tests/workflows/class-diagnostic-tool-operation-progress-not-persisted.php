<?php
/**
 * Tool Operation Progress Not Persisted
 *
 * Checks for persistence of tool operation progress across sessions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tools
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Tool_Operation_Progress_Not_Persisted Class
 *
 * Validates progress persistence for long-running tool operations.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Tool_Operation_Progress_Not_Persisted extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tool-progress-not-persisted';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tool Operation Progress Persistence';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates progress persistence across browser refreshes and sessions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests progress persistence mechanisms.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check for progress tracking
		if ( ! self::tracks_progress() ) {
			$issues[] = __( 'No progress tracking for long operations', 'wpshadow' );
		}

		// 2. Check for database persistence
		if ( ! self::persists_to_database() ) {
			$issues[] = __( 'Progress not saved to database', 'wpshadow' );
		}

		// 3. Check for status retrieval
		if ( ! self::can_retrieve_status() ) {
			$issues[] = __( 'Cannot retrieve operation status after page refresh', 'wpshadow' );
		}

		// 4. Check for progress display
		if ( ! self::displays_progress() ) {
			$issues[] = __( 'Progress indicator not shown to user', 'wpshadow' );
		}

		// 5. Check for multi-window awareness
		if ( ! self::handles_multiple_windows() ) {
			$issues[] = __( 'Multiple browser windows not aware of progress', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of persistence issues */
					__( '%d progress persistence issues found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/tool-progress-persistence',
				'recommendations' => array(
					__( 'Implement progress tracking for all operations', 'wpshadow' ),
					__( 'Save progress to database for persistence', 'wpshadow' ),
					__( 'Provide API to retrieve current operation status', 'wpshadow' ),
					__( 'Display live progress to user', 'wpshadow' ),
					__( 'Support multiple browser windows tracking same operation', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for progress tracking.
	 *
	 * @since  1.6030.2148
	 * @return bool True if progress tracked.
	 */
	private static function tracks_progress() {
		// Check for progress tracking option
		$progress = get_option( 'wpshadow_operation_progress' );
		if ( ! empty( $progress ) ) {
			return true;
		}

		// Check for progress tracking hook
		if ( has_filter( 'wpshadow_track_operation_progress' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for database persistence.
	 *
	 * @since  1.6030.2148
	 * @return bool True if progress persisted.
	 */
	private static function persists_to_database() {
		global $wpdb;

		// Check for progress table
		$table = $wpdb->prefix . 'wpshadow_operation_progress';
		$progress_table = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

		if ( $progress_table ) {
			return true;
		}

		// Check for post meta storage
		if ( has_filter( 'wpshadow_save_progress_to_meta' ) ) {
			return true;
		}

		// Check for option storage
		if ( has_filter( 'wpshadow_save_progress_to_option' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for status retrieval.
	 *
	 * @since  1.6030.2148
	 * @return bool True if status retrievable.
	 */
	private static function can_retrieve_status() {
		// Check for AJAX status endpoint
		if ( has_action( 'wp_ajax_wpshadow_get_operation_status' ) ) {
			return true;
		}

		// Check for REST endpoint
		if ( has_filter( 'wpshadow_rest_operation_status_endpoint' ) ) {
			return true;
		}

		// Check for status retrieval hook
		if ( has_filter( 'wpshadow_get_operation_status' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for progress display.
	 *
	 * @since  1.6030.2148
	 * @return bool True if progress displayed.
	 */
	private static function displays_progress() {
		// Check for progress bar/indicator script
		if ( has_filter( 'wpshadow_display_progress_indicator' ) ) {
			return true;
		}

		// Check for progress update script
		if ( has_filter( 'wpshadow_update_progress_display' ) ) {
			return true;
		}

		// Check for inline progress HTML
		if ( has_filter( 'wpshadow_progress_indicator_html' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for multi-window awareness.
	 *
	 * @since  1.6030.2148
	 * @return bool True if multi-window supported.
	 */
	private static function handles_multiple_windows() {
		// Check for broadcast channel support
		if ( has_filter( 'wpshadow_broadcast_progress' ) ) {
			return true;
		}

		// Check for shared storage (localStorage/sessionStorage)
		if ( has_filter( 'wpshadow_use_shared_storage' ) ) {
			return true;
		}

		// Check for polling mechanism
		if ( has_filter( 'wpshadow_poll_operation_status' ) ) {
			return true;
		}

		return false;
	}
}
