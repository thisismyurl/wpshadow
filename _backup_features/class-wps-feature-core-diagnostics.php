<?php
/**
 * Core Diagnostics feature definition.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Feature_Core_Diagnostics extends WPSHADOW_Abstract_Feature {
	
	/**
	 * Diagnostic check results cache.
	 *
	 * @var array
	 */
	private array $check_results = array();

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wpshadow_core_diagnostics',
				'name'               => __( 'Core Diagnostics', 'wpshadow' ),
				'description'        => __( 'Catch problems early - we monitor WordPress health and alert you when something goes wrong.', 'wpshadow' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => true,
				'widget_group'       => 'maintenance-tools',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-admin-tools',
				'category'           => 'diagnostics',
				'priority'           => 30,
				'sub_features'       => array(
					'core_updates'      => __( 'WordPress Core Update Checks', 'wpshadow' ),
					'php_version'       => __( 'PHP Version Compatibility', 'wpshadow' ),
					'database_health'   => __( 'Database Health Monitoring', 'wpshadow' ),
					'file_permissions'  => __( 'File Permission Verification', 'wpshadow' ),
					'security_headers'  => __( 'Security Headers Check', 'wpshadow' ),
					'debug_mode'        => __( 'Debug Mode Detection', 'wpshadow' ),
					'error_log'         => __( 'Error Log Monitoring', 'wpshadow' ),
					'ssl_check'         => __( 'SSL/HTTPS Configuration', 'wpshadow' ),
				),
				'settings'           => array(
					'check_interval'         => array(
						'type'        => 'select',
						'label'       => __( 'Check Interval', 'wpshadow' ),
						'description' => __( 'How often to run diagnostic checks', 'wpshadow' ),
						'default'     => 'daily',
						'options'     => array(
							'hourly'     => __( 'Hourly', 'wpshadow' ),
							'twicedaily' => __( 'Twice Daily', 'wpshadow' ),
							'daily'      => __( 'Daily', 'wpshadow' ),
							'weekly'     => __( 'Weekly', 'wpshadow' ),
						),
					),
					'autoload_threshold_mb'  => array(
						'type'        => 'number',
						'label'       => __( 'Autoload Data Threshold (MB)', 'wpshadow' ),
						'description' => __( 'Warn when autoloaded options exceed this size', 'wpshadow' ),
						'default'     => 1,
						'min'         => 0.5,
						'max'         => 10,
						'step'        => 0.5,
					),
					'log_size_threshold_mb'  => array(
						'type'        => 'number',
						'label'       => __( 'Error Log Size Threshold (MB)', 'wpshadow' ),
						'description' => __( 'Warn when error log exceeds this size', 'wpshadow' ),
						'default'     => 10,
						'min'         => 1,
						'max'         => 100,
						'step'        => 1,
					),
					'transient_cleanup_count' => array(
						'type'        => 'number',
						'label'       => __( 'Expired Transients Threshold', 'wpshadow' ),
						'description' => __( 'Warn when expired transients exceed this count', 'wpshadow' ),
						'default'     => 100,
						'min'         => 10,
						'max'         => 1000,
						'step'        => 10,
					),
					'show_admin_notices'     => array(
						'type'        => 'checkbox',
						'label'       => __( 'Show Admin Notices', 'wpshadow' ),
						'description' => __( 'Display admin notices for critical issues', 'wpshadow' ),
						'default'     => true,
					),
					'log_all_checks'         => array(
						'type'        => 'checkbox',
						'label'       => __( 'Log All Checks', 'wpshadow' ),
						'description' => __( 'Log passing checks in addition to warnings and errors', 'wpshadow' ),
						'default'     => false,
					),
				),
			)
		);
	}

	/**
	 * Register feature hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'init', array( $this, 'schedule_diagnostics' ) );
		add_action( 'wpshadow_daily_diagnostics', array( $this, 'run_diagnostics' ) );
		add_action( 'admin_init', array( $this, 'check_critical_issues' ) );
		add_action( 'admin_notices', array( $this, 'display_diagnostic_notices' ) );
		add_action( 'wp_ajax_wpshadow_run_diagnostics', array( $this, 'ajax_run_diagnostics' ) );
	}

	/**
	 * Schedule diagnostics check based on interval setting.
	 *
	 * @return void
	 */
	public function schedule_diagnostics(): void {
		$interval = $this->get_setting( 'check_interval', 'daily', false );
		$timestamp = wp_next_scheduled( 'wpshadow_daily_diagnostics' );
		if ( $timestamp ) {
			$current_schedule = wp_get_schedule( 'wpshadow_daily_diagnostics' );
			if ( $current_schedule !== $interval ) {
				wp_unschedule_event( $timestamp, 'wpshadow_daily_diagnostics' );
				$timestamp = false;
			}
		}
		if ( ! $timestamp ) {
			wp_schedule_event( time(), $interval, 'wpshadow_daily_diagnostics' );
		}
	}

	/**
	 * Get setting value with default fallback.
	 *
	 * @param string $setting_name Setting name.
	 * @param mixed  $default      Default value.
	 * @param bool   $network      Whether to get network setting.
	 * @return mixed Setting value.
	 */
	protected function get_setting( string $setting_name, $default = null, bool $network = false ) {
		$value = get_option( 'wpshadow_core_diagnostics_' . $setting_name );
		return false !== $value ? $value : $default;
	}

	/**
	 * Run full diagnostic checks.
	 *
	 * @return array Diagnostic results.
	 */
	public function run_diagnostics(): array {
		$results = array();
		if ( $this->is_sub_feature_enabled( 'core_updates' ) ) {
			$results['core_updates'] = $this->check_core_updates();
		}
		if ( $this->is_sub_feature_enabled( 'php_version' ) ) {
			$results['php_version'] = $this->check_php_version();
		}
		if ( $this->is_sub_feature_enabled( 'database_health' ) ) {
			$results['database_health'] = $this->check_database_health();
		}
		if ( $this->is_sub_feature_enabled( 'file_permissions' ) ) {
			$results['file_permissions'] = $this->check_file_permissions();
		}
		if ( $this->is_sub_feature_enabled( 'security_headers' ) ) {
			$results['security_headers'] = $this->check_security_headers();
		}
		if ( $this->is_sub_feature_enabled( 'debug_mode' ) ) {
			$results['debug_mode'] = $this->check_debug_mode();
		}
		if ( $this->is_sub_feature_enabled( 'error_log' ) ) {
			$results['error_log'] = $this->check_error_log();
		}
		if ( $this->is_sub_feature_enabled( 'ssl_check' ) ) {
			$results['ssl_check'] = $this->check_ssl_configuration();
		}
		$this->check_results = $results;
		set_transient( 'wpshadow_diagnostics_results', $results, DAY_IN_SECONDS );
		$this->log_diagnostic_issues( $results );
		return $results;
	}

	/**
	 * Check for critical issues that need immediate attention.
	 *
	 * @return void
	 */
	public function check_critical_issues(): void {
		$screen = get_current_screen();
		if ( ! $screen || 'dashboard' !== $screen->id ) {
			return;
		}
		$results = get_transient( 'wpshadow_diagnostics_results' );
		if ( false === $results ) {
			$results = $this->run_diagnostics();
		}
		$this->check_results = $results;
	}

	/**
	 * Check WordPress core updates.
	 *
	 * @return array Check result.
	 */
	private function check_core_updates(): array {
		require_once ABSPATH . 'wp-admin/includes/update.php';
		$updates = get_core_updates();
		if ( ! is_array( $updates ) || empty( $updates ) ) {
			return array( 'status' => 'pass', 'message' => __( 'WordPress core is up to date.', 'wpshadow' ) );
		}
		$update = reset( $updates );
		if ( 'upgrade' === $update->response ) {
			return array(
				'status'   => 'critical',
				'message'  => sprintf( __( 'WordPress core update available: %s', 'wpshadow' ), $update->version ),
				'severity' => 'high',
			);
		}
		return array( 'status' => 'pass', 'message' => __( 'WordPress core is up to date.', 'wpshadow' ) );
	}

	/**
	 * Check PHP version compatibility.
	 *
	 * @return array Check result.
	 */
	private function check_php_version(): array {
		$current_version = PHP_VERSION;
		$recommended_version = '8.1';
		$minimum_version = '7.4';
		if ( version_compare( $current_version, $minimum_version, '<' ) ) {
			return array(
				'status'   => 'critical',
				'message'  => sprintf( __( 'PHP version %1$s is outdated. Minimum required: %2$s', 'wpshadow' ), $current_version, $minimum_version ),
				'severity' => 'critical',
			);
		}
		if ( version_compare( $current_version, $recommended_version, '<' ) ) {
			return array(
				'status'   => 'warning',
				'message'  => sprintf( __( 'PHP version %1$s works but %2$s+ is recommended for better performance and security.', 'wpshadow' ), $current_version, $recommended_version ),
				'severity' => 'medium',
			);
		}
		return array( 'status' => 'pass', 'message' => sprintf( __( 'PHP version %s is up to date.', 'wpshadow' ), $current_version ) );
	}

	/**
	 * Check database health.
	 *
	 * @return array Check result.
	 */
	private function check_database_health(): array {
		global $wpdb;
		$issues = array();
		if ( ! $wpdb->check_connection( false ) ) {
			return array( 'status' => 'critical', 'message' => __( 'Database connection failed.', 'wpshadow' ), 'severity' => 'critical' );
		}
		$autoload_size = $wpdb->get_var( "SELECT SUM(LENGTH(option_value)) as autoload_size FROM {$wpdb->options} WHERE autoload = 'yes'" );
		$autoload_threshold = $this->get_setting( 'autoload_threshold_mb', 1, false ) * 1048576;
		if ( $autoload_size > $autoload_threshold ) {
			$issues[] = sprintf( __( 'Large autoloaded data: %1$s MB (threshold: %2$s MB)', 'wpshadow' ), round( $autoload_size / 1048576, 2 ), $this->get_setting( 'autoload_threshold_mb', 1, false ) );
		}
		$expired_transients = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d", '%_transient_timeout_%', time() ) );
		$transient_threshold = $this->get_setting( 'transient_cleanup_count', 100, false );
		if ( $expired_transients > $transient_threshold ) {
			$issues[] = sprintf( __( '%1$d expired transients should be cleaned up (threshold: %2$d).', 'wpshadow' ), $expired_transients, $transient_threshold );
		}
		if ( ! empty( $issues ) ) {
			return array( 'status' => 'warning', 'message' => implode( ' ', $issues ), 'severity' => 'medium' );
		}
		return array( 'status' => 'pass', 'message' => __( 'Database is healthy.', 'wpshadow' ) );
	}

	/**
	 * Check file permissions.
	 *
	 * @return array Check result.
	 */
	private function check_file_permissions(): array {
		$issues = array();
		$wp_config = ABSPATH . 'wp-config.php';
		if ( file_exists( $wp_config ) ) {
			$perms = fileperms( $wp_config );
			if ( $perms & 0020 || $perms & 0002 ) {
				$issues[] = __( 'wp-config.php has insecure permissions (should be 400 or 440).', 'wpshadow' );
			}
		}
		if ( ! is_writable( WP_CONTENT_DIR ) ) {
			$issues[] = __( 'wp-content directory is not writable.', 'wpshadow' );
		}
		if ( ! empty( $issues ) ) {
			return array( 'status' => 'warning', 'message' => implode( ' ', $issues ), 'severity' => 'medium' );
		}
		return array( 'status' => 'pass', 'message' => __( 'File permissions are correctly configured.', 'wpshadow' ) );
	}

	/**
	 * Check security headers.
	 *
	 * @return array Check result.
	 */
	private function check_security_headers(): array {
		$home_url = home_url();
		$response = wp_remote_head( $home_url, array( 'timeout' => 10 ) );
		if ( is_wp_error( $response ) ) {
			return array( 'status' => 'warning', 'message' => __( 'Unable to check security headers.', 'wpshadow' ) );
		}
		$headers = wp_remote_retrieve_headers( $response );
		$issues = array();
		if ( empty( $headers['x-frame-options'] ) ) {
			$issues[] = __( 'X-Frame-Options header missing (clickjacking protection).', 'wpshadow' );
		}
		if ( empty( $headers['x-content-type-options'] ) ) {
			$issues[] = __( 'X-Content-Type-Options header missing (MIME sniffing protection).', 'wpshadow' );
		}
		if ( ! empty( $issues ) ) {
			return array( 'status' => 'warning', 'message' => implode( ' ', $issues ), 'severity' => 'low' );
		}
		return array( 'status' => 'pass', 'message' => __( 'Security headers are properly configured.', 'wpshadow' ) );
	}

	/**
	 * Check if debug mode is enabled in production.
	 *
	 * @return array Check result.
	 */
	private function check_debug_mode(): array {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$message = __( 'WP_DEBUG is enabled.', 'wpshadow' );
			if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
				$message .= ' ' . __( 'Errors are being displayed publicly (security risk).', 'wpshadow' );
				return array( 'status' => 'critical', 'message' => $message, 'severity' => 'high' );
			}
			return array( 'status' => 'warning', 'message' => $message . ' ' . __( 'Consider disabling in production.', 'wpshadow' ), 'severity' => 'medium' );
		}
		return array( 'status' => 'pass', 'message' => __( 'Debug mode is not enabled.', 'wpshadow' ) );
	}

	/**
	 * Check error log for recent critical errors.
	 *
	 * @return array Check result.
	 */
	private function check_error_log(): array {
		$log_file = ini_get( 'error_log' );
		if ( empty( $log_file ) || ! file_exists( $log_file ) ) {
			$log_file = WP_CONTENT_DIR . '/debug.log';
		}
		if ( ! file_exists( $log_file ) || ! is_readable( $log_file ) ) {
			return array( 'status' => 'info', 'message' => __( 'No error log file found or not readable.', 'wpshadow' ) );
		}
		$log_size = filesize( $log_file );
		$log_threshold = $this->get_setting( 'log_size_threshold_mb', 10, false ) * 1048576;
		if ( $log_size > $log_threshold ) {
			return array( 'status' => 'warning', 'message' => sprintf( __( 'Error log is large (%1$s MB) and should be reviewed/cleared (threshold: %2$s MB).', 'wpshadow' ), round( $log_size / 1048576, 2 ), $this->get_setting( 'log_size_threshold_mb', 10, false ) ), 'severity' => 'medium' );
		}
		$handle = fopen( $log_file, 'r' );
		if ( false === $handle ) {
			return array( 'status' => 'info', 'message' => __( 'Unable to read error log.', 'wpshadow' ) );
		}
		fseek( $handle, max( 0, $log_size - 51200 ), SEEK_SET );
		$content = fread( $handle, 51200 );
		fclose( $handle );
		$fatal_count = substr_count( $content, 'Fatal error' ) + substr_count( $content, 'PHP Fatal error' );
		if ( $fatal_count > 0 ) {
			return array( 'status' => 'critical', 'message' => sprintf( __( '%d fatal error(s) detected in recent logs.', 'wpshadow' ), $fatal_count ), 'severity' => 'high' );
		}
		return array( 'status' => 'pass', 'message' => __( 'No critical errors in recent logs.', 'wpshadow' ) );
	}

	/**
	 * Check SSL/HTTPS configuration.
	 *
	 * @return array Check result.
	 */
	private function check_ssl_configuration(): array {
		$home_url = home_url();
		$site_url = site_url();
		if ( ! is_ssl() && ( strpos( $home_url, 'https://' ) === 0 || strpos( $site_url, 'https://' ) === 0 ) ) {
			return array( 'status' => 'critical', 'message' => __( 'Site URLs are configured for HTTPS but SSL is not detected.', 'wpshadow' ), 'severity' => 'high' );
		}
		if ( ! defined( 'FORCE_SSL_ADMIN' ) ) {
			return array( 'status' => 'warning', 'message' => __( 'FORCE_SSL_ADMIN is not defined. Consider forcing SSL for admin area.', 'wpshadow' ), 'severity' => 'low' );
		}
		if ( is_ssl() ) {
			return array( 'status' => 'pass', 'message' => __( 'SSL/HTTPS is properly configured.', 'wpshadow' ) );
		}
		return array( 'status' => 'info', 'message' => __( 'Site is not using HTTPS. Consider enabling SSL for security.', 'wpshadow' ), 'severity' => 'low' );
	}

	/**
	 * Log diagnostic issues found.
	 *
	 * @param array $results Diagnostic results.
	 * @return void
	 */
	private function log_diagnostic_issues( array $results ): void {
		$log_all = $this->get_setting( 'log_all_checks', false, false );
		foreach ( $results as $check => $result ) {
			if ( in_array( $result['status'], array( 'critical', 'warning' ), true ) ) {
				$this->log_activity(
					ucfirst( str_replace( '_', ' ', $check ) ),
					$result['message'],
					$result['status']
				);
			} elseif ( $log_all && 'pass' === $result['status'] ) {
				$this->log_activity(
					ucfirst( str_replace( '_', ' ', $check ) ),
					$result['message'],
					'info'
				);
			}
		}
	}

	/**
	 * Display diagnostic notices in admin.
	 *
	 * @return void
	 */
	public function display_diagnostic_notices(): void {
		if ( ! $this->get_setting( 'show_admin_notices', true, false ) ) {
			return;
		}
		if ( empty( $this->check_results ) ) {
			return;
		}
		$critical_issues = array();
		foreach ( $this->check_results as $check => $result ) {
			if ( 'critical' === $result['status'] ) {
				$critical_issues[] = $result['message'];
			}
		}
		if ( ! empty( $critical_issues ) ) {
			echo '<div class="notice notice-error is-dismissible">';
			echo '<p><strong>' . esc_html__( 'WPShadow: Critical Issues Detected', 'wpshadow' ) . '</strong></p>';
			echo '<ul style="list-style: disc; margin-left: 20px;">';
			foreach ( $critical_issues as $issue ) {
				echo '<li>' . esc_html( $issue ) . '</li>';
			}
			echo '</ul>';
			echo '</div>';
		}
	}

	/**
	 * AJAX handler to run diagnostics manually.
	 *
	 * @return void
	 */
	public function ajax_run_diagnostics(): void {
		check_ajax_referer( 'wpshadow_diagnostics', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}
		$results = $this->run_diagnostics();
		wp_send_json_success( array(
			'results' => $results,
			'message' => __( 'Diagnostics completed successfully.', 'wpshadow' ),
		) );
	}

	/**
	 * Cleanup on disable.
	 *
	 * @return void
	 */
	public function on_disable(): void {
		$timestamp = wp_next_scheduled( 'wpshadow_daily_diagnostics' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'wpshadow_daily_diagnostics' );
		}
		delete_transient( 'wpshadow_diagnostics_results' );
	}
}
