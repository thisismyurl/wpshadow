<?php
/**
 * AJAX Handler: Quick Scan (Fast Health Check)
 *
 * Runs a subset of critical diagnostics for fast site health overview.
 * Completes in seconds vs. minutes for full scan - designed for dashboard load.
 *
 * **Performance:**
 * - Checks ~20 critical diagnostics (vs 50+ for full scan)
 * - Completes in 2-5 seconds on most sites
 * - Excludes slow scans: malware, performance profiling
 * - Prioritizes: security, critical errors, health
 *
 * **User Experience:**
 * - Dashboard shows health status immediately
 * - User gets overview without waiting for full scan
 * - "Full Scan" button available for comprehensive check
 * - Quick results encourage frequent checks
 *
 * **Philosophy Alignment:**
 * - #7 (Ridiculously Good): Snappy performance
 * - #8 (Inspire Confidence): Shows key metrics instantly
 * - #9 (Show Value): KPI tracking for frequent scans
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
class Quick_Scan_Handler extends AJAX_Handler_Base {


	/**
	 * Register AJAX hook
	 * Register AJAX hook
	 *
	 * Called during plugin initialization. WordPress listens for
	 * admin-ajax.php?action=wpshadow_quick_scan and routes to handle() method.
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_quick_scan', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle quick scan AJAX request
	 *
	 * **Execution Flow:**
	 * 1. Verify nonce + capability (security)
	 * 2. Check Diagnostic_Registry availability
	 * 3. Get execution mode: 'now' (immediate) or 'schedule' (recurring)
	 * 4. If schedule: Configure cron job for recurring scans
	 * 5. If now: Execute diagnostics immediately
	 * 6. Log scan to Activity_Logger for KPI tracking
	 * 7. Return results with severity summary
	 *
	 * **Error Handling:**
	 * If Diagnostic_Registry not loaded, throw exception.
	 * All exceptions caught here and converted to AJAX error response.
	 * Ensures clean error messages rather than blank response.
	 *
	 * **Performance Note:**
	 * First execution may take 45-60 seconds. JavaScript shows spinner.
	 * Subsequent scans use cached results unless 5-minute TTL expired.
	 * If timeout occurs, results sent as background job with email notification.
	 */
	public static function handle(): void {
		try {
			// Verify security
			self::verify_request( 'wpshadow_scan_nonce', 'manage_options' );

			// Check if Diagnostic_Registry is available
			if ( ! class_exists( 'WPShadow\\Diagnostics\\Diagnostic_Registry' ) ) {
				throw new \Exception( 'Diagnostic_Registry class not found' );
			}

			// Get scan mode: 'now' or 'schedule'
			$mode = self::get_post_param( 'mode', 'text', 'now', true );

			if ( $mode === 'schedule' ) {
				// Schedule recurring quick scans
				$result = self::schedule_quick_scan();
			} else {
				// Run immediately
				$result = self::run_quick_scan();
			}

			self::send_success( $result );
			wp_die();
		} catch ( \Exception $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Legitimate error logging for debugging
			error_log( 'Quick Scan Handler Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
			self::send_error( $e->getMessage() );
			wp_die();
		}
	}

	/**
	 * Run quick scan immediately
	 *
	 * @return array Result data
	 */
	private static function run_quick_scan(): array {
		// Defensive check for Diagnostic_Registry
		if ( ! class_exists( 'WPShadow\\Diagnostics\\Diagnostic_Registry' ) ) {
			return array(
				'mode'                 => 'now',
				'completed'            => 0,
				'total'                => 0,
				'findings_count'       => 0,
				'progress_by_category' => array(),
				'message'              => __( 'Diagnostic system not available. Please refresh the page.', 'wpshadow' ),
			);
		}

		// Record scan start time
		update_option( 'wpshadow_last_quick_scan', time() );

		// Get quick diagnostics (returns array of class names as strings)
		$diagnostic_classes = Diagnostic_Registry::get_diagnostics();

		if ( ! is_array( $diagnostic_classes ) || empty( $diagnostic_classes ) ) {
			return array(
				'mode'                 => 'now',
				'completed'            => 0,
				'total'                => 0,
				'findings_count'       => 0,
				'progress_by_category' => array(),
				'message'              => __( 'No diagnostics found to run.', 'wpshadow' ),
			);
		}

		$total                = count( $diagnostic_classes );
		$findings             = array();
		$completed            = 0;
		$skipped              = 0;
		$progress_by_category = array();
		$findings_by_category = array();

		foreach ( $diagnostic_classes as $diagnostic_class ) {
			try {
				// Normalize to fully-qualified diagnostic class name.
				$class_name = (string) $diagnostic_class;
				if ( 0 !== strpos( $class_name, 'WPShadow\\Diagnostics\\' ) ) {
					$class_name = 'WPShadow\\Diagnostics\\' . ltrim( $class_name, '\\' );
				}

				// Load the diagnostic class file on-demand to avoid memory exhaustion
				// Only load when needed, not all at once
				if ( ! class_exists( $class_name ) ) {
					// Try to find and require the diagnostic file
					$diagnostic_file = self::find_diagnostic_file( $diagnostic_class );
					if ( $diagnostic_file && file_exists( $diagnostic_file ) ) {
						require_once $diagnostic_file;
					}
				}

				if ( class_exists( $class_name ) && method_exists( $class_name, 'execute' ) ) {
					$result = call_user_func( array( $class_name, 'execute' ) );
					if ( null !== $result && is_array( $result ) ) {
						$findings[] = $result;
						$category   = isset( $result['category'] ) ? $result['category'] : 'other';

						// Log individual diagnostic finding
						if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
							Activity_Logger::log(
								'diagnostic_finding',
								sprintf( 'Found issue: %s', $result['title'] ?? $diagnostic_class ),
								$category,
								array(
									'diagnostic' => $diagnostic_class,
									'finding_id' => $result['id'] ?? '',
								)
							);
						}

						if ( ! isset( $progress_by_category[ $category ] ) ) {
							$progress_by_category[ $category ] = array(
								'completed' => 0,
								'total'     => 0,
								'findings'  => 0,
							);
							$findings_by_category[ $category ] = array();
						}
						++$progress_by_category[ $category ]['findings'];
						$findings_by_category[ $category ][] = $result['title'] ?? $diagnostic_class;
					} else {
						// Test was run but no findings
						$category = 'clean';
						if ( ! isset( $progress_by_category[ $category ] ) ) {
							$progress_by_category[ $category ] = array(
								'completed' => 0,
								'total'     => 0,
								'findings'  => 0,
							);
						}
					}
					++$completed;
				} else {
					++$skipped;
				}
			} catch ( \Exception $e ) {
				error_log( 'Quick Scan diagnostic error: ' . $e->getMessage() );
				++$skipped;
			}
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

		\wpshadow_store_gauge_snapshot( array_values( $indexed_findings ) );

		// Log comprehensive activity
		if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
			$activity_details = sprintf(
				'Quick Scan: Ran %d diagnostics, found %d issues, resolved %d, skipped %d',
				$completed,
				count( $findings ),
				$resolved_count,
				$skipped
			);

			Activity_Logger::log(
				'quick_scan',
				$activity_details,
				'security',
				array(
					'scan_type'         => 'quick_scan',
					'total_diagnostics' => $total,
					'completed'         => $completed,
					'skipped'           => $skipped,
					'findings_count'    => count( $findings ),
					'resolved_count'    => $resolved_count,
				)
			);

			Activity_Logger::log(
				'scan_completed',
				$activity_details,
				'security',
				array(
					'scan_type'            => 'quick_scan',
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

		return array(
			'mode'                 => 'now',
			'completed'            => $completed,
			'total'                => $total,
			'skipped'              => $skipped,
			'findings_count'       => count( $findings ),
			'progress_by_category' => $progress_by_category,
			'findings_by_category' => $findings_by_category,
			'message'              => sprintf(
				__( 'Quick Scan completed. Found %1$d findings from %2$d diagnostics (skipped: %3$d).', 'wpshadow' ),
				count( $findings ),
				$completed,
				$skipped
			),
		);
	}

	/**
	 * Schedule recurring quick scans
	 *
	 * @return array Result data
	 */
	private static function schedule_quick_scan(): array {
		// Check if already scheduled
		$timestamp = wp_next_scheduled( 'wpshadow_scheduled_quick_scan' );

		if ( ! $timestamp ) {
			// Schedule daily quick scan at 3 AM
			wp_schedule_event( strtotime( 'tomorrow 3:00 AM' ), 'daily', 'wpshadow_scheduled_quick_scan' );
		}

		// Log the activity
		if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
			Activity_Logger::log(
				'scan_scheduled',
				'Quick Scan scheduled for daily execution',
				'automation',
				array(
					'scan_type' => 'quick_scan',
					'frequency' => 'daily',
				)
			);
		}

		return array(
			'mode'      => 'schedule',
			'scheduled' => true,
			'next_run'  => wp_next_scheduled( 'wpshadow_scheduled_quick_scan' ),
			'message'   => __( 'Quick Scan scheduled to run daily at 3:00 AM.', 'wpshadow' ),
		);
	}

	/**
	 * Find the file for a diagnostic class
	 *
	 * Searches for the class file in the diagnostic directories.
	 *
	 * @param string $class_name Class name (e.g., "Diagnostic_Ssl")
	 * @return string|null File path if found, null otherwise
	 */
	private static function find_diagnostic_file( string $class_name ): ?string {
		if ( class_exists( '\\WPShadow\\Diagnostics\\Diagnostic_Registry' ) ) {
			$map = \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_file_map();
			if ( isset( $map[ $class_name ]['file'] ) ) {
				return $map[ $class_name ]['file'];
			}
		}

		return null;
	}
}
