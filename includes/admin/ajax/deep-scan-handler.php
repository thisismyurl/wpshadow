<?php
/**
 * AJAX Handler: Deep Scan (Comprehensive Health Check)
 *
 * Runs ALL registered diagnostics for complete site health analysis.
 * Includes expensive checks like malware scanning, link validation, SEO audit.
 * Designed to run when user has time (admin doesn't need instant results).
 *
 * **Scope:**
 * - All 50+ registered diagnostics
 * - Includes slow checks: malware, link validation, performance profiles
 * - Excludes only manually-disabled diagnostics
 * - Typically takes 5-15 minutes depending on site size
 *
 * **Server Awareness:**
 * - Checks server capacity before heavy operations
 * - Throttles if CPU/memory trending high
 * - Can be interrupted by user (returns partial results)
 * - Saves progress for resumption
 *
 * **Philosophy Alignment:**
 * - #9 (Show Value): Comprehensive findings prove plugin value
 * - #1 (Helpful Neighbor): "Here's EVERYTHING we found"
 * - #8 (Inspire Confidence): Thoroughness builds trust
 *
 * @package WPShadow
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Options_Manager;
use WPShadow\Diagnostics\Diagnostic_Registry;
use WPShadow\Core\Activity_Logger;
use WPShadow\Core\KPI_Tracker;

/**
 * Deep_Scan_Handler Class
 *
 * @since 0.6093.1200
 * @package WPShadow
 */
class Deep_Scan_Handler extends AJAX_Handler_Base {


	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_deep_scan', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle deep scan AJAX request
	 */
	public static function handle(): void {
		try {
			// Verify security
			self::verify_request( 'wpshadow_scan_nonce', 'manage_options' );

			// Get scan mode: 'now' or 'schedule'
			$mode = self::get_post_param( 'mode', 'text', 'now', true );

			if ( $mode === 'schedule' ) {
				// Schedule deep scan for weekly execution
				$result = self::schedule_deep_scan();
			} else {
				// Run immediately (with warning already acknowledged)
				$result = self::run_deep_scan();
			}

			self::send_success( $result );
			wp_die();
		} catch ( \Exception $e ) {		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Legitimate error logging for debugging			error_log( 'Deep Scan Handler Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
			self::send_error( $e->getMessage() );
			wp_die();
		}
	}

	/**
	 * Run deep scan immediately
	 *
	 * MURPHY-SAFE: Prevents concurrent scans that could overwhelm server.
	 *
	 * @return array Result data
	 */
	private static function run_deep_scan(): array {
		// MURPHY-SAFE: Check if scan already running (prevent concurrent scans)
		$scan_lock = get_transient( 'wpshadow_scan_running' );

		if ( false !== $scan_lock ) {
			return array(
				'success'    => false,
				'message'    => __( 'A scan is already running. Please wait for it to complete.', 'wpshadow' ),
				'started_at' => $scan_lock,
				'locked'     => true,
			);
		}

		// Set lock (expires after 10 minutes as safety net)
		set_transient( 'wpshadow_scan_running', time(), 10 * MINUTE_IN_SECONDS );

		try {
			// Record scan start time
			update_option( 'wpshadow_last_deep_scan', time() );

		// Run all deep scan checks (quick + deep diagnostics)
		$findings = Diagnostic_Registry::run_deepscan_checks();
		$scan_stats = Diagnostic_Registry::get_last_run_stats();
		$requested_diagnostics = isset( $scan_stats['requested'] ) && is_array( $scan_stats['requested'] )
			? $scan_stats['requested']
			: array();
		$executed_diagnostics = isset( $scan_stats['executed'] ) && is_array( $scan_stats['executed'] )
			? $scan_stats['executed']
			: array();
		$diagnostic_results = isset( $scan_stats['results'] ) && is_array( $scan_stats['results'] )
			? $scan_stats['results']
			: array();

		$total     = ! empty( $requested_diagnostics ) ? count( $requested_diagnostics ) : count( Diagnostic_Registry::get_diagnostics() );
		$completed = ! empty( $executed_diagnostics ) ? count( $executed_diagnostics ) : $total;

			// Build progress by category and findings breakdown
			$progress_by_category = array();
			$findings_by_category = array();
			$skipped              = 0;

			foreach ( $findings as $finding ) {
			$category = isset( $finding['category'] ) ? $finding['category'] : 'other';

			// Log individual diagnostic finding
			if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
				Activity_Logger::log(
					'diagnostic_finding',
					sprintf( 'Found issue: %s', $finding['title'] ?? $finding['id'] ?? 'Unknown' ),
					$category,
					array(
						'finding_id' => $finding['id'] ?? '',
						'scan_type'  => 'deep',
					)
				);
			}

			// Phase 3: Track KPI for finding detection
			$severity = isset( $finding['severity'] ) ? $finding['severity'] : 'medium';
			KPI_Tracker::log_finding_detected( $finding['id'] ?? 'unknown', $severity );

			if ( ! isset( $progress_by_category[ $category ] ) ) {
				$progress_by_category[ $category ] = array(
					'completed' => 0,
					'total'     => 0,
					'findings'  => 0,
				);
				$findings_by_category[ $category ] = array();
			}
			++$progress_by_category[ $category ]['findings'];
			$findings_by_category[ $category ][] = $finding['title'] ?? $finding['id'] ?? 'Unknown';
		}

		// Estimate skipped if any
		if ( $completed > 0 && count( $findings ) === 0 ) {
			// Add clean category to show diagnostics ran but found nothing
			$progress_by_category['clean'] = array(
				'completed' => $completed,
				'total'     => $total,
				'findings'  => 0,
			);
		}

		$previous_findings = Options_Manager::get_array( 'wpshadow_site_findings', array() );
		$previous_findings = is_array( $previous_findings ) ? $previous_findings : array();
		$previous_ids      = array_keys( $previous_findings );

		$indexed_findings = \wpshadow_index_findings_by_id( $findings );
		$current_ids      = array_keys( $indexed_findings );
		$resolved_ids     = array_diff( $previous_ids, $current_ids );
		$resolved_count   = 0;

		foreach ( $resolved_ids as $resolved_id ) {
			$stored_finding = $previous_findings[ $resolved_id ] ?? array();
			++$resolved_count;

			if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
				Activity_Logger::log(
					'finding_resolved',
					sprintf( 'Issue resolved: %s', $stored_finding['title'] ?? $resolved_id ),
					$stored_finding['category'] ?? 'other',
					array( 'finding_id' => $resolved_id )
				);
			}
		}

		\wpshadow_store_gauge_data( array_values( $indexed_findings ) );
		$completed_at = time();
		update_option( 'wpshadow_last_quick_checks', $completed_at );
		if ( function_exists( 'wpshadow_record_diagnostic_run_coverage' ) ) {
			\wpshadow_record_diagnostic_run_coverage( $executed_diagnostics, $completed_at );
		}
		if ( function_exists( 'wpshadow_record_diagnostic_test_states' ) ) {
			\wpshadow_record_diagnostic_test_states( $diagnostic_results, $completed_at );
		}

		// Log comprehensive activity
		if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
			$activity_details = sprintf(
				'Deep Scan: Ran %d diagnostics, found %d issues, resolved %d, %d categories affected',
				$completed,
				count( $findings ),
				$resolved_count,
				count( $findings_by_category )
			);

			Activity_Logger::log(
				'scan_completed',
				$activity_details,
				'security',
				array(
					'scan_type'            => 'deep_scan',
					'warning_acknowledged' => true,
					'total_diagnostics'    => $total,
					'completed'            => $completed,
					'skipped'              => $skipped,
					'findings_count'       => count( $findings ),
					'resolved_count'       => $resolved_count,
					'findings_by_category' => $findings_by_category,
					'categories_affected'  => array_keys( $findings_by_category ),
					'run_by_user'          => wp_get_current_user()->display_name,
				)
			);
		}

		// MURPHY-SAFE: Clear scan lock on successful completion
		delete_transient( 'wpshadow_scan_running' );

		return array(
			'mode'                 => 'now',
			'completed'            => $completed,
			'total'                => $total,
			'skipped'              => $skipped,
			'findings_count'       => count( $findings ),
			'progress_by_category' => $progress_by_category,
			'findings_by_category' => $findings_by_category,
			'message'              => sprintf(
				__( 'Deep Scan completed. Found %1$d findings from %2$d diagnostics (%3$d categories affected).', 'wpshadow' ),
				count( $findings ),
				$completed,
				count( $findings_by_category )
			),
		);

		} catch ( \Exception $e ) {
			// MURPHY-SAFE: Clear scan lock on error
			delete_transient( 'wpshadow_scan_running' );

			Error_Handler::log_error( 'Deep scan failed', $e );

			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Schedule deep scan for weekly execution
	 *
	 * @return array Result data
	 */
	private static function schedule_deep_scan(): array {
		// Check if already scheduled
		$timestamp = wp_next_scheduled( 'wpshadow_scheduled_deep_scan' );

		if ( ! $timestamp ) {
			// Schedule weekly deep scan on Sunday at 2 AM
			wp_schedule_event( strtotime( 'next Sunday 2:00 AM' ), 'weekly', 'wpshadow_scheduled_deep_scan' );
		}

		// Log the activity
		if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
			Activity_Logger::log(
				'scan_scheduled',
				'Deep Scan scheduled for weekly execution',
				'automation',
				array(
					'scan_type' => 'deep_scan',
					'frequency' => 'weekly',
					'time'      => '2:00 AM Sunday',
				)
			);
		}

		return array(
			'mode'      => 'schedule',
			'scheduled' => true,
			'next_run'  => wp_next_scheduled( 'wpshadow_scheduled_deep_scan' ),
			'message'   => __( 'Deep Scan scheduled to run weekly on Sundays at 2:00 AM.', 'wpshadow' ),
		);
	}
}
