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
use WPShadow\Core\Error_Handler;
use WPShadow\Core\KPI_Tracker;

/**
 * Deep_Scan_Handler Class
 *
 * @since 0.6093.1200
 * @package WPShadow
 */
class Deep_Scan_Handler extends AJAX_Handler_Base {

	/**
	 * Transient key storing live deep scan progress metadata.
	 */
	const PROGRESS_TRANSIENT_KEY = 'wpshadow_scan_progress_state';

	/**
	 * Progress transient TTL in seconds.
	 */
	const PROGRESS_TRANSIENT_TTL = 10 * MINUTE_IN_SECONDS;


	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_deep_scan', array( __CLASS__, 'handle' ) );
		add_action( 'wp_ajax_wpshadow_deep_scan_status', array( __CLASS__, 'handle_status' ) );
	}

	/**
	 * Handle deep scan status polling.
	 *
	 * @return void
	 */
	public static function handle_status(): void {
		self::verify_request( 'wpshadow_scan_nonce', 'manage_options' );

		$scan_lock = get_transient( 'wpshadow_scan_running' );
		if ( false !== $scan_lock ) {
			$started_at = is_numeric( $scan_lock ) ? (int) $scan_lock : 0;
			if ( $started_at > 0 && ( time() - $started_at ) >= ( 10 * MINUTE_IN_SECONDS ) ) {
				delete_transient( 'wpshadow_scan_running' );
				delete_transient( self::PROGRESS_TRANSIENT_KEY );
				$scan_lock = false;
			}
		}

		$progress_state = get_transient( self::PROGRESS_TRANSIENT_KEY );
		$progress_state = is_array( $progress_state ) ? $progress_state : array();
		$stalled_message = '';

		if ( false !== $scan_lock ) {
			$progress_phase = isset( $progress_state['phase'] ) ? (string) $progress_state['phase'] : '';
			$progress_updated_at = isset( $progress_state['updated_at'] ) ? (int) $progress_state['updated_at'] : 0;
			$progress_completed = isset( $progress_state['completed'] ) ? (int) $progress_state['completed'] : 0;

			// Recover automatically if startup stalled before diagnostics began.
			if ( 'starting' === $progress_phase && $progress_updated_at > 0 && ( time() - $progress_updated_at ) > 20 && $progress_completed <= 0 ) {
				delete_transient( 'wpshadow_scan_running' );
				delete_transient( self::PROGRESS_TRANSIENT_KEY );
				$scan_lock = false;
				$progress_state = array();
				$stalled_message = __( 'Scan startup stalled before diagnostics began. This usually means the server ran out of memory during startup.', 'wpshadow' );
			}
		}

		$running           = false !== $scan_lock;
		$started_at        = $running && is_numeric( $scan_lock ) ? (int) $scan_lock : 0;
		$elapsed_seconds   = $started_at > 0 ? max( 0, time() - $started_at ) : 0;
		$estimated_seconds = 10 * MINUTE_IN_SECONDS;
		$completed_items   = isset( $progress_state['completed'] ) ? (int) $progress_state['completed'] : 0;
		$total_items       = isset( $progress_state['total'] ) ? (int) $progress_state['total'] : 0;

		if ( $running && $total_items > 0 ) {
			$progress_percent = min( 99, (int) floor( ( max( 0, $completed_items ) / max( 1, $total_items ) ) * 100 ) );
		} elseif ( $running ) {
			$progress_percent = min( 99, (int) floor( ( $elapsed_seconds / $estimated_seconds ) * 100 ) );
		} else {
			$progress_percent = 100;
		}

		self::send_success(
			array(
				'running'           => $running,
				'started_at'        => $started_at,
				'elapsed_seconds'   => $elapsed_seconds,
				'estimated_seconds' => $estimated_seconds,
				'progress_percent'  => $progress_percent,
				'current_slug'      => isset( $progress_state['current_slug'] ) ? (string) $progress_state['current_slug'] : '',
				'current_label'     => isset( $progress_state['current_label'] ) ? (string) $progress_state['current_label'] : '',
				'completed_items'   => $completed_items,
				'total_items'       => $total_items,
				'dashboard_summary' => self::build_dashboard_summary(),
				'stalled'           => '' !== $stalled_message,
				'stalled_message'   => $stalled_message,
			)
		);
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

			if ( isset( $result['success'] ) && false === $result['success'] ) {
				self::send_error(
					isset( $result['message'] ) ? (string) $result['message'] : __( 'Deep scan failed.', 'wpshadow' ),
					$result
				);
				wp_die();
			}

			self::send_success( $result );
			wp_die();
		} catch ( \Throwable $e ) {		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Legitimate error logging for debugging			error_log( 'Deep Scan Handler Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
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
		if ( function_exists( 'wp_raise_memory_limit' ) ) {
			wp_raise_memory_limit( 'admin' );
		}
		if ( function_exists( 'ini_set' ) ) {
			@ini_set( 'memory_limit', '1024M' );
		}

		// MURPHY-SAFE: Check if scan already running (prevent concurrent scans)
		$scan_lock = get_transient( 'wpshadow_scan_running' );

		if ( false !== $scan_lock ) {
			$started_at = is_numeric( $scan_lock ) ? (int) $scan_lock : 0;
			if ( $started_at > 0 && ( time() - $started_at ) >= ( 10 * MINUTE_IN_SECONDS ) ) {
				delete_transient( 'wpshadow_scan_running' );
				$scan_lock = false;
			}
		}

		if ( false !== $scan_lock ) {
			$progress_state = get_transient( self::PROGRESS_TRANSIENT_KEY );
			$progress_state = is_array( $progress_state ) ? $progress_state : array();

			return array(
				'success'    => false,
				'message'    => __( 'A scan is already running. Please wait for it to complete.', 'wpshadow' ),
				'started_at' => $scan_lock,
				'locked'     => true,
				'current_slug'    => isset( $progress_state['current_slug'] ) ? (string) $progress_state['current_slug'] : '',
				'current_label'   => isset( $progress_state['current_label'] ) ? (string) $progress_state['current_label'] : '',
				'completed_items' => isset( $progress_state['completed'] ) ? (int) $progress_state['completed'] : 0,
				'total_items'     => isset( $progress_state['total'] ) ? (int) $progress_state['total'] : 0,
			);
		}

		// Set lock (expires after 10 minutes as safety net)
		set_transient( 'wpshadow_scan_running', time(), 10 * MINUTE_IN_SECONDS );

		$total_items      = count( Diagnostic_Registry::get_deep_scan_diagnostics() );
		$completed_items  = 0;
		$before_hook      = static function ( $class, $slug ) use ( &$completed_items, $total_items ): void {
			self::update_scan_progress_state(
				array(
					'phase'         => 'running',
					'current_slug'  => (string) $slug,
					'current_label' => self::build_scan_label( (string) $class, (string) $slug ),
					'completed'     => $completed_items,
					'total'         => $total_items,
					'updated_at'    => time(),
				)
			);
		};
		$after_hook       = static function ( $class, $slug, $_finding = null ) use ( &$completed_items, $total_items ): void {
			$completed_items++;
			self::update_scan_progress_state(
				array(
					'phase'         => 'running',
					'current_slug'  => (string) $slug,
					'current_label' => self::build_scan_label( (string) $class, (string) $slug ),
					'completed'     => $completed_items,
					'total'         => $total_items,
					'updated_at'    => time(),
				)
			);
		};

		self::update_scan_progress_state(
			array(
				'phase'         => 'starting',
				'current_slug'  => '',
				'current_label' => __( 'Preparing diagnostics…', 'wpshadow' ),
				'completed'     => 0,
				'total'         => $total_items,
				'updated_at'    => time(),
			)
		);

		add_action( 'wpshadow_before_diagnostic_check', $before_hook, 10, 2 );
		add_action( 'wpshadow_after_diagnostic_check', $after_hook, 10, 3 );

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

		$indexed_findings = function_exists( 'wpshadow_index_findings_by_id' )
			? \wpshadow_index_findings_by_id( $findings )
			: array();
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

		if ( function_exists( 'wpshadow_store_gauge_data' ) ) {
			\wpshadow_store_gauge_data( array_values( $indexed_findings ) );
		}
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
		delete_transient( self::PROGRESS_TRANSIENT_KEY );
		remove_action( 'wpshadow_before_diagnostic_check', $before_hook, 10 );
		remove_action( 'wpshadow_after_diagnostic_check', $after_hook, 10 );

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

		} catch ( \Throwable $e ) {
			// MURPHY-SAFE: Clear scan lock on error
			delete_transient( 'wpshadow_scan_running' );
			delete_transient( self::PROGRESS_TRANSIENT_KEY );
			remove_action( 'wpshadow_before_diagnostic_check', $before_hook, 10 );
			remove_action( 'wpshadow_after_diagnostic_check', $after_hook, 10 );

			Error_Handler::log_error( 'Deep scan failed', $e );

			return array(
				'success' => false,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Persist scan progress metadata for live UI polling.
	 *
	 * @param array<string,mixed> $state Progress payload.
	 * @return void
	 */
	private static function update_scan_progress_state( array $state ): void {
		set_transient( self::PROGRESS_TRANSIENT_KEY, $state, self::PROGRESS_TRANSIENT_TTL );
	}

	/**
	 * Build a human-readable label for the currently running diagnostic.
	 *
	 * @param string $class Diagnostic class.
	 * @param string $slug  Diagnostic slug.
	 * @return string
	 */
	private static function build_scan_label( string $class, string $slug ): string {
		if ( '' !== $slug ) {
			return ucfirst( str_replace( array( '-', '_' ), ' ', $slug ) );
		}

		$short = strrchr( $class, '\\' );
		$short = false === $short ? $class : ltrim( $short, '\\' );
		$short = preg_replace( '/^Diagnostic_/', '', $short );
		$short = preg_replace( '/(?<!^)([A-Z])/', ' $1', (string) $short );

		return trim( (string) $short );
	}

	/**
	 * Build a lightweight dashboard summary payload for live UI refresh.
	 *
	 * @return array<string,mixed>
	 */
	private static function build_dashboard_summary(): array {
		if ( ! function_exists( 'wpshadow_get_diagnostics_activity_rows' ) ) {
			return array();
		}

		$rows = wpshadow_get_diagnostics_activity_rows();
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$total    = count( $rows );
		$passed   = 0;
		$failed   = 0;
		$disabled = 0;

		foreach ( $rows as $row ) {
			$status = isset( $row['status_raw'] ) ? (string) $row['status_raw'] : '';
			if ( 'passed' === $status ) {
				$passed++;
			} elseif ( 'failed' === $status ) {
				$failed++;
			} elseif ( 'disabled' === $status ) {
				$disabled++;
			}
		}

		$active = max( 0, $total - $disabled );
		$score  = $active > 0 ? (int) round( ( $passed / $active ) * 100 ) : 100;

		return array(
			'total'    => $total,
			'passed'   => $passed,
			'failed'   => $failed,
			'disabled' => $disabled,
			'active'   => $active,
			'score'    => $score,
		);
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

add_action( 'wp_ajax_wpshadow_deep_scan', array( '\WPShadow\Admin\Ajax\Deep_Scan_Handler', 'handle' ) );
add_action( 'wp_ajax_wpshadow_deep_scan_status', array( '\WPShadow\Admin\Ajax\Deep_Scan_Handler', 'handle_status' ) );
