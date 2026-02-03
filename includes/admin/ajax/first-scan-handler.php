<?php
declare( strict_types=1 );

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Diagnostics\Diagnostic_Registry;

/**
 * AJAX Handler: Dashboard First Scan
 *
 * Action: wp_ajax_wpshadow_first_scan
 * Nonce: wpshadow_first_scan_nonce
 * Capability: manage_options
 *
 * Philosophy: Show value (#9) - Run initial scan to get quick baseline
 *
 * @package WPShadow
 */
class First_Scan_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_first_scan', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle first scan AJAX request
	 * Actually runs the quick scan and returns progress + findings
	 */
	public static function handle(): void {
		try {
			// Verify security
			self::verify_request( 'wpshadow_first_scan_nonce', 'manage_options' );

			// Record scan start time
			update_option( 'wpshadow_last_quick_scan', time() );

			// Log the activity
			if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
				try {
					\WPShadow\Core\Activity_Logger::log(
						'diagnostic_run',
						'First Quick Scan initiated by user',
						'security',
						array( 'scan_type' => 'quick_scan_first_time' )
					);
				} catch ( \Exception $e ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Legitimate error logging
					error_log( 'Activity log failed: ' . $e->getMessage() );
				}
			}

			// Verify Diagnostic_Registry exists
			if ( ! class_exists( 'WPShadow\\Diagnostics\\Diagnostic_Registry' ) ) {
				throw new \Exception( 'Diagnostic_Registry class not found' );
			}

			// Get list of diagnostics to run
			$diagnostics = Diagnostic_Registry::get_diagnostics();

			if ( ! is_array( $diagnostics ) || empty( $diagnostics ) ) {
				throw new \Exception( 'No diagnostics found' );
			}

			$total          = count( $diagnostics );
			$progress_steps = array();
			$findings       = array();

			// Build progress steps and run diagnostics
			foreach ( $diagnostics as $index => $diagnostic_class ) {
				$progress = ( ( $index + 1 ) / $total ) * 100;

				// Get friendly name from class
				$friendly_name = str_replace( array( 'Diagnostic_', '_' ), array( '', ' ' ), $diagnostic_class );

				$progress_steps[] = array(
					'step'       => $index + 1,
					'total'      => $total,
					'progress'   => round( $progress, 1 ),
					'diagnostic' => $friendly_name,
				);

				// Run individual diagnostic with error handling
				try {
					$class_name = 'WPShadow\\Diagnostics\\' . $diagnostic_class;

					if ( class_exists( $class_name ) && method_exists( $class_name, 'check' ) ) {
						$result = call_user_func( array( $class_name, 'check' ) );
						if ( null !== $result && is_array( $result ) ) {
							$findings[] = $result;
						}
					}
				} catch ( \Exception $e ) {
					error_log( "Diagnostic $diagnostic_class failed: " . $e->getMessage() );
					// Continue with next diagnostic instead of crashing
				}
			}

			// Count issues by severity
			$issue_counts = array(
				'critical' => 0,
				'high'     => 0,
				'medium'   => 0,
				'low'      => 0,
			);

			foreach ( $findings as $finding ) {
				if ( isset( $finding['severity'] ) && isset( $issue_counts[ $finding['severity'] ] ) ) {
					++$issue_counts[ $finding['severity'] ];
				}
			}

			self::send_success(
				<?php
				/**
				 * AJAX Handler: Dashboard First Scan (Onboarding Critical Moment)
				 *
				 * Runs initial diagnostic scan during plugin activation/onboarding.
				 * This is often the user's FIRST impression of WPShadow - critical moment!
				 *
				 * **Strategic Importance:**
				 * - User just activated plugin → Run scan to prove value immediately
				 * - Discovers 5-10 issues instantly → Builds confidence
				 * - Shows quick fixes available → Demonstrates auto-fix capability
				 * - Sets baseline for tracking improvements → Shows ROI over time
				 *
				 * **Optimizations:**
				 * - Subset of fast checks (2-5 seconds, not full 30-second scan)
				 * - Skip expensive scans to show results quickly
				 * - Prioritize high-impact issues
				 * - Save results to show on dashboard immediately
				 *
				 * **Philosophy Alignment:**
				 * - #9 (Show Value): "Look what we found immediately"
				 * - #7 (Ridiculously Good): Fast results impress users
				 * - #1 (Helpful Neighbor): "Here's how we can help"
				 *
				 * @package WPShadow
				 * @since 1.2601.2148
				 */
			error_log( 'Stack trace: ' . $e->getTraceAsString() );
			self::send_error(
				sprintf(
					__( 'Scan failed: %s', 'wpshadow' ),
					$e->getMessage()
				)
			);
		}
	}
}
