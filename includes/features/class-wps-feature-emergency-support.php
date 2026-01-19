<?php
/**
 * Feature: Emergency Support on Critical Errors
 *
 * Monitors for critical PHP errors and provides emergency support options.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.76000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Emergency_Support
 *
 * Emergency Support on Critical Errors implementation.
 */
final class WPSHADOW_Feature_Emergency_Support extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'              => 'emergency-support',
				'name'            => __( 'Emergency Support on Critical Errors', 'wpshadow' ),
				'description'     => __( 'Monitor for critical PHP errors and surface support options automatically.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => true,
				'version'         => '1.0.0',
				'widget_group'    => 'maintenance-tools',
			)
		);

		$this->log_activity( 'feature_initialized', 'Emergency Support feature initialized', 'info' );
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		register_shutdown_function( array( $this, 'handle_fatal_error' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Handle fatal PHP errors.
	 *
	 * @return void
	 */
	public function handle_fatal_error(): void {
		$error = error_get_last();

		if ( ! $error || ! ( $error['type'] & ( E_ERROR | E_PARSE | E_COMPILE_ERROR | E_COMPILE_WARNING ) ) ) {
			return;
		}

		$this->log_critical_error( $error );
		set_transient( 'wpshadow_last_fatal_error', $error, HOUR_IN_SECONDS );
	}

	/**
	 * Log critical error to database.
	 *
	 * @param array $error Error details from error_get_last().
	 * @return void
	 */
	private function log_critical_error( array $error ): void {
		$errors_key = 'wpshadow_critical_errors';
		$errors     = get_option( $errors_key, array() );

		$critical = array(
			'id'        => wp_generate_uuid4(),
			'timestamp' => time(),
			'type'      => $error['type'] ?? 'unknown',
			'message'   => $error['message'] ?? '',
			'file'      => $error['file'] ?? '',
			'line'      => $error['line'] ?? 0,
			'severity'  => $this->get_severity_level( $error['type'] ?? 0 ),
		);

		// Keep last 20 errors.
		if ( count( $errors ) > 20 ) {
			array_shift( $errors );
		}

		$errors[] = $critical;
		update_option( $errors_key, $errors );

		$this->log_activity( 'critical_error', $critical['message'], 'error' );
	}

	/**
	 * Get severity level from error type.
	 *
	 * @param int $type PHP error type.
	 * @return string Severity level.
	 */
	private function get_severity_level( int $type ): string {
		switch ( $type ) {
			case E_ERROR:
			case E_PARSE:
				return 'FATAL';
			case E_COMPILE_ERROR:
			case E_COMPILE_WARNING:
				return 'CRITICAL';
			default:
				return 'ERROR';
		}
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Array of Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['wpshadow_critical_errors'] = array(
			'label' => __( 'Critical Errors', 'wpshadow' ),
			'test'  => array( $this, 'test_critical_errors' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for critical errors.
	 *
	 * @return array Test result.
	 */
	public function test_critical_errors(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Critical Errors Monitor', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Security', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'Emergency support monitoring is disabled.', 'wpshadow' ),
				'test'        => 'wpshadow_critical_errors',
			);
		}

		$errors_key = 'wpshadow_critical_errors';
		$errors     = get_option( $errors_key, array() );

		// Get errors from last 24 hours.
		$recent = array_filter(
			$errors,
			static function ( $error ) {
				return ( time() - $error['timestamp'] ) < DAY_IN_SECONDS;
			}
		);

		if ( empty( $recent ) ) {
			return array(
				'label'       => __( 'No Critical Errors', 'wpshadow' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Security', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'Your site has not encountered critical errors in the last 24 hours.', 'wpshadow' ),
				'test'        => 'wpshadow_critical_errors',
			);
		}

		$count = count( $recent );
		$last  = end( $recent );

		return array(
			'label'       => sprintf(
				/* translators: %d: number of critical errors */
				__( '%d Critical Error(s)', 'wpshadow' ),
				(int) $count
			),
			'status'      => 'critical',
			'badge'       => array(
				'label' => __( 'Security', 'wpshadow' ),
				'color' => 'red',
			),
			'description' => sprintf(
				/* translators: %s: last error message */
				__( 'Most recent: %s in %s on line %d', 'wpshadow' ),
				$last['message'] ?? 'Unknown',
				$last['file'] ?? 'Unknown',
				(int) ( $last['line'] ?? 0 )
			),
			'test'        => 'wpshadow_critical_errors',
		);
	}
}
