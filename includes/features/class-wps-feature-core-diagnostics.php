<?php declare(strict_types=1);
/**
 * Feature: Core Diagnostics
 *
 * Comprehensive health checks for WordPress core, PHP, database, and security.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

final class WPSHADOW_Feature_Core_Diagnostics extends WPSHADOW_Abstract_Feature {

	private array $check_results = array();

	public function __construct() {
		parent::__construct( array(
			'id'          => 'core-diagnostics',
			'name'        => __( 'Core Diagnostics', 'wpshadow' ),
			'description' => __( 'Monitor WordPress core, PHP, database, and security health.', 'wpshadow' ),
			'sub_features' => array(
				'core_updates'       => __( 'Core Updates', 'wpshadow' ),
				'php_version'        => __( 'PHP Version Check', 'wpshadow' ),
				'database_health'    => __( 'Database Health', 'wpshadow' ),
				'file_permissions'   => __( 'File Permissions', 'wpshadow' ),
				'security_headers'   => __( 'Security Headers', 'wpshadow' ),
				'debug_mode'         => __( 'Debug Mode Check', 'wpshadow' ),
				'error_log'          => __( 'Error Log Monitoring', 'wpshadow' ),
				'ssl_check'          => __( 'SSL Configuration', 'wpshadow' ),
			),
		) );

		$this->register_default_settings( array(
			'core_updates'       => true,
			'php_version'        => true,
			'database_health'    => true,
			'file_permissions'   => true,
			'security_headers'   => true,
			'debug_mode'         => true,
			'error_log'          => true,
			'ssl_check'          => true,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Run diagnostics daily
		add_action( 'wp_scheduled_delete', array( $this, 'run_scheduled_diagnostics' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Run scheduled diagnostics.
	 */
	public function run_scheduled_diagnostics(): void {
		$results = $this->run_all_checks();
		set_transient( 'wpshadow_diagnostics_results', $results, DAY_IN_SECONDS );
	}

	/**
	 * Run all diagnostic checks.
	 */
	private function run_all_checks(): array {
		$results = array();

		if ( $this->is_sub_feature_enabled( 'core_updates', true ) ) {
			$results['core_updates'] = $this->check_core_updates();
		}

		if ( $this->is_sub_feature_enabled( 'php_version', true ) ) {
			$results['php_version'] = $this->check_php_version();
		}

		if ( $this->is_sub_feature_enabled( 'database_health', true ) ) {
			$results['database_health'] = $this->check_database_health();
		}

		if ( $this->is_sub_feature_enabled( 'file_permissions', true ) ) {
			$results['file_permissions'] = $this->check_file_permissions();
		}

		if ( $this->is_sub_feature_enabled( 'security_headers', true ) ) {
			$results['security_headers'] = $this->check_security_headers();
		}

		if ( $this->is_sub_feature_enabled( 'debug_mode', true ) ) {
			$results['debug_mode'] = $this->check_debug_mode();
		}

		if ( $this->is_sub_feature_enabled( 'error_log', true ) ) {
			$results['error_log'] = $this->check_error_log();
		}

		if ( $this->is_sub_feature_enabled( 'ssl_check', true ) ) {
			$results['ssl_check'] = $this->check_ssl_configuration();
		}

		return $results;
	}

	/**
	 * Check WordPress core updates.
	 */
	private function check_core_updates(): array {
		require_once ABSPATH . 'wp-admin/includes/update.php';
		$updates = get_core_updates();

		if ( ! is_array( $updates ) || empty( $updates ) ) {
			return array( 'status' => 'good', 'message' => __( 'WordPress core is up to date.', 'wpshadow' ) );
		}

		$update = reset( $updates );
		if ( 'upgrade' === $update->response ) {
			return array(
				'status'   => 'critical',
				'message'  => sprintf( __( 'WordPress core update available: %s', 'wpshadow' ), $update->version ),
			);
		}

		return array( 'status' => 'good', 'message' => __( 'WordPress core is up to date.', 'wpshadow' ) );
	}

	/**
	 * Check PHP version.
	 */
	private function check_php_version(): array {
		$current = PHP_VERSION;
		$recommended = '8.1';
		$minimum = '7.4';

		if ( version_compare( $current, $minimum, '<' ) ) {
			return array(
				'status'   => 'critical',
				'message'  => sprintf( __( 'PHP %s is outdated. Minimum: %s', 'wpshadow' ), $current, $minimum ),
			);
		}

		if ( version_compare( $current, $recommended, '<' ) ) {
			return array(
				'status'   => 'warning',
				'message'  => sprintf( __( 'PHP %s works but %s+ recommended.', 'wpshadow' ), $current, $recommended ),
			);
		}

		return array(
			'status'   => 'good',
			'message'  => sprintf( __( 'PHP %s is optimal.', 'wpshadow' ), $current ),
		);
	}

	/**
	 * Check database health.
	 */
	private function check_database_health(): array {
		global $wpdb;

		if ( ! $wpdb->check_connection( false ) ) {
			return array(
				'status'   => 'critical',
				'message'  => __( 'Database connection failed.', 'wpshadow' ),
			);
		}

		// Check for expired transients
		$expired = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
				'%_transient_timeout_%',
				time()
			)
		);

		if ( $expired > 1000 ) {
			return array(
				'status'   => 'warning',
				'message'  => sprintf( __( '%d expired transients should be cleaned up.', 'wpshadow' ), $expired ),
			);
		}

		return array(
			'status'   => 'good',
			'message'  => __( 'Database is healthy.', 'wpshadow' ),
		);
	}

	/**
	 * Check file permissions.
	 */
	private function check_file_permissions(): array {
		$wp_config = ABSPATH . 'wp-config.php';

		if ( file_exists( $wp_config ) ) {
			$perms = fileperms( $wp_config );
			// Check if world-readable or group-writable
			if ( ( $perms & 0020 ) || ( $perms & 0002 ) ) {
				return array(
					'status'   => 'warning',
					'message'  => __( 'wp-config.php has insecure permissions.', 'wpshadow' ),
				);
			}
		}

		if ( ! is_writable( WP_CONTENT_DIR ) ) {
			return array(
				'status'   => 'warning',
				'message'  => __( 'wp-content directory is not writable.', 'wpshadow' ),
			);
		}

		return array(
			'status'   => 'good',
			'message'  => __( 'File permissions are secure.', 'wpshadow' ),
		);
	}

	/**
	 * Check security headers.
	 */
	private function check_security_headers(): array {
		$response = wp_remote_head( home_url(), array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			return array(
				'status'   => 'warning',
				'message'  => __( 'Unable to check security headers.', 'wpshadow' ),
			);
		}

		$headers = wp_remote_retrieve_headers( $response );
		$missing = array();

		if ( empty( $headers['x-frame-options'] ) ) {
			$missing[] = 'X-Frame-Options';
		}

		if ( empty( $headers['x-content-type-options'] ) ) {
			$missing[] = 'X-Content-Type-Options';
		}

		if ( ! empty( $missing ) ) {
			return array(
				'status'   => 'warning',
				'message'  => sprintf( __( 'Missing headers: %s', 'wpshadow' ), implode( ', ', $missing ) ),
			);
		}

		return array(
			'status'   => 'good',
			'message'  => __( 'Security headers configured.', 'wpshadow' ),
		);
	}

	/**
	 * Check debug mode.
	 */
	private function check_debug_mode(): array {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
				return array(
					'status'   => 'critical',
					'message'  => __( 'WP_DEBUG enabled with errors displayed publicly.', 'wpshadow' ),
				);
			}

			return array(
				'status'   => 'warning',
				'message'  => __( 'WP_DEBUG enabled. Disable in production.', 'wpshadow' ),
			);
		}

		return array(
			'status'   => 'good',
			'message'  => __( 'Debug mode disabled.', 'wpshadow' ),
		);
	}

	/**
	 * Check error log.
	 */
	private function check_error_log(): array {
		$log_file = ini_get( 'error_log' );
		if ( empty( $log_file ) || ! file_exists( $log_file ) ) {
			$log_file = WP_CONTENT_DIR . '/debug.log';
		}

		if ( ! file_exists( $log_file ) || ! is_readable( $log_file ) ) {
			return array(
				'status'   => 'info',
				'message'  => __( 'No error log found.', 'wpshadow' ),
			);
		}

		$size = filesize( $log_file );
		$threshold = 10 * 1048576; // 10 MB

		if ( $size > $threshold ) {
			return array(
				'status'   => 'warning',
				'message'  => sprintf( __( 'Error log is large (%s MB).', 'wpshadow' ), round( $size / 1048576, 2 ) ),
			);
		}

		// Check for recent fatal errors
		$handle = fopen( $log_file, 'r' );
		if ( false !== $handle ) {
			fseek( $handle, max( 0, $size - 51200 ), SEEK_SET );
			$content = fread( $handle, 51200 );
			fclose( $handle );

			$fatals = substr_count( $content, 'Fatal error' );
			if ( $fatals > 0 ) {
				return array(
					'status'   => 'critical',
					'message'  => sprintf( __( '%d fatal error(s) in recent logs.', 'wpshadow' ), $fatals ),
				);
			}
		}

		return array(
			'status'   => 'good',
			'message'  => __( 'No critical errors in log.', 'wpshadow' ),
		);
	}

	/**
	 * Check SSL configuration.
	 */
	private function check_ssl_configuration(): array {
		if ( ! is_ssl() ) {
			return array(
				'status'   => 'info',
				'message'  => __( 'Site is not using HTTPS.', 'wpshadow' ),
			);
		}

		if ( ! defined( 'FORCE_SSL_ADMIN' ) || ! FORCE_SSL_ADMIN ) {
			return array(
				'status'   => 'warning',
				'message'  => __( 'FORCE_SSL_ADMIN not enabled. Consider forcing SSL in admin.', 'wpshadow' ),
			);
		}

		return array(
			'status'   => 'good',
			'message'  => __( 'SSL/HTTPS is properly configured.', 'wpshadow' ),
		);
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['core_diagnostics'] = array(
			'label'  => __( 'Core Diagnostics', 'wpshadow' ),
			'test'   => array( $this, 'test_diagnostics' ),
		);

		return $tests;
	}

	public function test_diagnostics(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Core Diagnostics', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable core diagnostics for comprehensive health monitoring.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'core_diagnostics',
			);
		}

		$results = get_transient( 'wpshadow_diagnostics_results' );
		if ( false === $results ) {
			$results = $this->run_all_checks();
		}

		$critical_count = 0;
		$warning_count = 0;
		foreach ( $results as $check ) {
			if ( 'critical' === $check['status'] ) {
				$critical_count++;
			} elseif ( 'warning' === $check['status'] ) {
				$warning_count++;
			}
		}

		$status = 'good';
		if ( $critical_count > 0 ) {
			$status = 'critical';
		} elseif ( $warning_count > 0 ) {
			$status = 'recommended';
		}

		return array(
			'label'       => __( 'Core Diagnostics', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => sprintf(
				__( 'Diagnostics: %d critical, %d warning issues detected.', 'wpshadow' ),
				$critical_count,
				$warning_count
			),
			'actions'     => '',
			'test'        => 'core_diagnostics',
		);
	}
}
