<?php
/**
 * Feature: Emergency Support on Critical Errors
 *
 * Surface support options on error pages.
 * Injects professional support CTA when WordPress displays critical errors.
 *
 * @package WPShadow\Features
 * @since 1.2601.76000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * Emergency Support Feature
 *
 * Monitors for critical PHP errors and provides emergency support options.
 */
final class WPSHADOW_Feature_Emergency_Support extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'emergency-support',
				'name'               => __( 'Emergency Support on Critical Errors', 'wpshadow' ),
				'description'        => __( 'Monitor for critical PHP errors and surface support options automatically.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
			'widget_group'       => 'maintenance-tools',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-warning',
				'category'           => 'security',
				'priority'           => 25,
			)
		);
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'wp_loaded', array( $this, 'initialize' ) );
	}

	/**
	 * Initialize the feature.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Register fatal error handler.
		register_shutdown_function( array( $this, 'handle_fatal_error' ) );

		// Hook into health check.
		add_filter( 'site_status_tests', array( $this, 'add_support_to_health_check' ) );

		// Dashboard widget for pending issues.
		add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );

		$this->log_activity( 'feature_initialized', 'Emergency Support feature initialized', 'info' );
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

		// Log critical error.
		$this->log_critical_error( $error );

		// Only show support prompt in admin (not frontend).
		if ( ! is_admin() && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		// Store error in transient for display on next page load.
		set_transient( 'wpshadow_last_fatal_error', $error, 3600 );
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

		// Send admin alert.
		$this->send_critical_error_alert( $critical );

		// Log activity.
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
	 * Send email alert for critical error.
	 *
	 * @param array $error Error data.
	 * @return void
	 */
	private function send_critical_error_alert( array $error ): void {
		$admin_email = get_option( 'admin_email' );

		$subject = sprintf(
			'🚨 %s: Critical Error on %s',
			$error['severity'],
			get_bloginfo( 'name' )
		);

		$message = sprintf(
			"A critical error occurred on %s\n\n" .
			"Error: %s\n" .
			"File: %s (line %d)\n" .
			"Timestamp: %s\n\n" .
			"Get Help:\n" .
			"Dashboard: %s\n" .
			"Professional Support: %s\n",
			get_bloginfo( 'url' ),
			$error['message'],
			$error['file'],
			$error['line'],
			wp_date( 'Y-m-d H:i:s', $error['timestamp'] ),
			admin_url( 'admin.php?page=wpshadow' ),
			'https://wpshadow.com/support'
		);

		wp_mail( $admin_email, $subject, $message );
	}

	/**
	 * Add support options to health check.
	 *
	 * @param array $tests Health check tests.
	 * @return array Modified tests.
	 */
	public function add_support_to_health_check( array $tests ): array {
		// Add a custom test that checks for recent critical errors.
		$tests['direct']['wpshadow_critical_errors'] = array(
			'label'    => __( 'Critical Errors', 'wpshadow' ),
			'test'     => array( $this, 'test_critical_errors' ),
			'has_rest' => true,
			'async'    => false,
		);

		return $tests;
	}

	/**
	 * Test for critical errors (for health check).
	 *
	 * @return array Health check result.
	 */
	public function test_critical_errors(): array {
		$errors_key = 'wpshadow_critical_errors';
		$errors     = get_option( $errors_key, array() );

		// Get errors from last 24 hours.
		$recent = array_filter(
			$errors,
			static function ( $error ) {
				return ( time() - $error['timestamp'] ) < 86400;
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
				'actions'     => '',
				'test'        => 'wpshadow_critical_errors',
			);
		}

		$count    = count( $recent );
		$last     = end( $recent );
		$actions  = '<a href="' . esc_url( admin_url( 'admin.php?page=wpshadow' ) ) . '">';
		$actions .= esc_html__( 'View Error Details', 'wpshadow' );
		$actions .= '</a>';

		return array(
			'label'       => sprintf(
				/* translators: %d: number of critical errors */
				__( '%d Critical Error(s)', 'wpshadow' ),
				$count
			),
			'status'      => 'critical',
			'badge'       => array(
				'label' => __( 'Security', 'wpshadow' ),
				'color' => 'red',
			),
			'description' => sprintf(
				/* translators: %s: last error message */
				__( 'Most recent: %s', 'wpshadow' ),
				$last['message'] ?? ''
			),
			'actions'     => $actions,
			'test'        => 'wpshadow_critical_errors',
		);
	}

	/**
	 * Register dashboard widget.
	 *
	 * @return void
	 */
	public function register_dashboard_widget(): void {
		wp_add_dashboard_widget(
			'wpshadow_critical_errors',
			__( 'Critical Errors Alert', 'wpshadow' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget.
	 *
	 * @return void
	 */
	public function render_dashboard_widget(): void {
		$errors_key = 'wpshadow_critical_errors';
		$errors     = get_option( $errors_key, array() );

		if ( empty( $errors ) ) {
			echo '<p>' . esc_html__( 'No critical errors recorded.', 'wpshadow' ) . '</p>';
			return;
		}

		// Show last 5 errors.
		$recent = array_slice( $errors, -5 );
		echo '<table style="width: 100%; font-size: 12px;">';
		foreach ( $recent as $error ) {
			$time = wp_date( 'Y-m-d H:i', $error['timestamp'] );
			echo '<tr>';
			echo '<td style="padding: 4px;">' . esc_html( $time ) . '</td>';
			echo '<td style="padding: 4px;">' . esc_html( $error['severity'] ) . '</td>';
			echo '<td style="padding: 4px;">' . esc_html( substr( $error['message'], 0, 50 ) ) . '...</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
}
