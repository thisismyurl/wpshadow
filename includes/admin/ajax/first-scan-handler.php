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
	 * Runs the initial diagnostics scan and returns progress + findings
	 */
	public static function handle(): void {
		try {
			// Verify security
			self::verify_request( 'wpshadow_first_scan_nonce', 'manage_options' );

			// Log the activity
			if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
				try {
					\WPShadow\Core\Activity_Logger::log(
						'diagnostic_run',
						'First diagnostic run initiated by user',
						'security',
						array( 'scan_type' => 'initial_scan' )
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

					if ( class_exists( $class_name ) ) {
						// force = true: first scan is user-initiated — run every enabled diagnostic.
						if ( method_exists( $class_name, 'execute' ) ) {
							$result = call_user_func( array( $class_name, 'execute' ), true );
						} elseif ( method_exists( $class_name, 'check' ) ) {
							$result = call_user_func( array( $class_name, 'check' ) );
						} else {
							$result = null;
						}
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
				array(
					'progress'     => $progress_steps,
					'findings'     => $findings,
					'issue_counts' => $issue_counts,
					'total'        => $total,
				)
			);

		} catch ( \Exception $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Legitimate error logging
			error_log( 'First scan failed: ' . $e->getMessage() );
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
