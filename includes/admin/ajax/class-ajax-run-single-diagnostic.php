<?php
/**
 * AJAX Handler: Run Single Diagnostic
 *
 * Executes one diagnostic class on demand from the dashboard detail panel.
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since      0.6091.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Diagnostics\Diagnostic_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Run Single Diagnostic Handler
 *
 * @since 0.6091.1200
 */
class AJAX_Run_Single_Diagnostic extends AJAX_Handler_Base {

	/**
	 * Register the AJAX handler.
	 *
	 * @since 0.6091.1200
	 * @return void
	 */
	public static function register() {
		add_action( 'wp_ajax_wpshadow_run_single_diagnostic', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the AJAX request.
	 *
	 * @since 0.6091.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		self::verify_manage_options_request( 'wpshadow_security_scan' );

		// get_post_param uses wp_unslash which correctly restores backslashes that WordPress's
		// wp_magic_quotes() doubled. Do NOT read $_POST directly.
		$raw_class_name = self::get_post_param( 'class_name', 'text', '', true );
		$class_name     = ltrim( $raw_class_name, '\\' );
		if ( 0 !== strpos( $class_name, 'WPShadow\\Diagnostics\\' ) ) {
			$class_name = 'WPShadow\\Diagnostics\\' . $class_name;
		}

		if ( ! self::ensure_diagnostic_class_loaded( $class_name ) ) {
			self::send_error( __( 'Diagnostic class could not be loaded.', 'wpshadow' ) );
		}

		if ( ! method_exists( $class_name, 'execute' ) && ! method_exists( $class_name, 'check' ) ) {
			self::send_error( __( 'Diagnostic class is not executable.', 'wpshadow' ) );
		}

		$disabled_diagnostics = self::get_array_option( 'wpshadow_disabled_diagnostic_classes', array() );
		if ( in_array( $class_name, $disabled_diagnostics, true ) ) {
			self::send_error( __( 'This diagnostic is inactive and cannot be run.', 'wpshadow' ) );
		}

		try {
			$result = method_exists( $class_name, 'execute' )
				? $class_name::execute()
				: $class_name::check();

			$completed_at = time();
			$run_key      = self::get_run_key_from_class_name( $class_name );
			update_option( 'wpshadow_last_run_' . $run_key, $completed_at );

			$status     = ( is_array( $result ) && ! empty( $result ) ) ? 'failed' : 'passed';
			$finding_id = ( is_array( $result ) && isset( $result['id'] ) ) ? (string) $result['id'] : '';
			$category   = ( is_array( $result ) && isset( $result['category'] ) ) ? (string) $result['category'] : '';

			if ( function_exists( 'wpshadow_record_diagnostic_run_coverage' ) ) {
				\wpshadow_record_diagnostic_run_coverage( array( $class_name ), $completed_at );
			}

			if ( function_exists( 'wpshadow_record_diagnostic_test_states' ) ) {
				\wpshadow_record_diagnostic_test_states(
					array(
						$class_name => array(
							'status'     => $status,
							'category'   => $category,
							'finding_id' => $finding_id,
						),
					),
					$completed_at
				);
			}

			self::send_success(
				array(
					'class_name' => $class_name,
					'run_key'    => $run_key,
					'status'     => $status,
					'finding'    => is_array( $result ) ? $result : null,
					'message'    => ( 'failed' === $status )
						? __( 'Diagnostic ran and found an issue.', 'wpshadow' )
						: __( 'Diagnostic ran successfully with no issues found.', 'wpshadow' ),
				)
			);
		} catch ( \Throwable $exception ) {
			self::send_error( __( 'Diagnostic run failed. Please check debug logs for details.', 'wpshadow' ) );
		}
	}

	/**
	 * Ensure a diagnostic class is available.
	 *
	 * @since  0.6091.1200
	 * @param  string $class_name Fully-qualified class name.
	 * @return bool True when class is loaded.
	 */
	private static function ensure_diagnostic_class_loaded( string $class_name ): bool {
		if ( class_exists( $class_name ) ) {
			return true;
		}

		if ( ! class_exists( Diagnostic_Registry::class ) ) {
			return false;
		}

		$file_map = Diagnostic_Registry::get_diagnostic_file_map();
		if ( ! is_array( $file_map ) || empty( $file_map ) ) {
			return false;
		}

		$short_name = str_replace( 'WPShadow\\Diagnostics\\', '', $class_name );
		$candidates = array( $class_name, $short_name );

		foreach ( $candidates as $candidate ) {
			if ( isset( $file_map[ $candidate ]['file'] ) ) {
				$file = (string) $file_map[ $candidate ]['file'];
				if ( '' !== $file && file_exists( $file ) ) {
					require_once $file;
				}
			}
		}

		return class_exists( $class_name );
	}

	/**
	 * Build run-key from class name.
	 *
	 * @since  0.6091.1200
	 * @param  string $class_name Fully-qualified class name.
	 * @return string Sanitized run key.
	 */
	private static function get_run_key_from_class_name( string $class_name ): string {
		$short_name = str_replace( 'WPShadow\\Diagnostics\\', '', $class_name );
		$short_name = strtolower( str_replace( '_', '-', $short_name ) );

		return sanitize_key( $short_name );
	}
}
