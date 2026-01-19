<?php declare(strict_types=1);
/**
 * Core Diagnostics Feature
 *
 * Comprehensive system health monitoring and diagnostics for WordPress installations
 * including core updates, PHP version, database health, permissions, security headers,
 * debug mode detection, error log monitoring, and SSL configuration.
 *
 * @package    WPShadow
 * @subpackage Features
 * @since      1.0.0
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

/**
 * Core Diagnostics Feature Class
 *
 * Performs automated health checks on critical WordPress system components and
 * provides alerts for issues requiring attention.
 *
 * @since 1.0.0
 */
final class WPSHADOW_Feature_Core_Diagnostics extends WPSHADOW_Abstract_Feature {

	/**
	 * Diagnostic check results cache.
	 *
	 * @var array
	 */
	private array $check_results = array();

	/**
	 * Feature constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'core-diagnostics',
				'name'               => __( 'Health Check-Up', 'wpshadow' ),
				'description_short'  => __( 'Regularly check if your website is running smoothly', 'wpshadow' ),
				'description_long'   => __( 'Automatically checks important parts of your website every day to make sure everything is working properly. Looks for updates you might need, checks if your site is secure, and warns you about problems before they cause trouble. Like a regular doctor visit for your website, helping catch issues early when they\'re easier to fix.', 'wpshadow' ),
				'description_wizard' => __( 'Turn on automatic daily checks to catch website problems early. You\'ll get simple alerts if something needs attention, helping prevent bigger issues down the road.', 'wpshadow' ),
				'aliases'            => array( 'system check', 'health check', 'diagnostics', 'monitoring', 'system status', 'health monitoring' ),
				'sub_features'       => array(
					'core_updates'       => array(
						'name'               => __( 'Core Update Monitoring', 'wpshadow' ),
						'description_short'  => __( 'Check for available WordPress updates', 'wpshadow' ),
						'description_long'   => __( 'Monitors WordPress.org for core updates and alerts administrators when new versions are available. Distinguishes between minor security updates, major feature releases, and development builds. Provides update details including version numbers, release dates, and changelog links. Critical severity for security updates, standard severity for feature updates. Checks update API daily and caches results to minimize API calls. Helps maintain security and feature parity with latest WordPress releases.', 'wpshadow' ),
						'description_wizard' => __( 'Get notified when WordPress core updates are available so you can keep your site secure and up-to-date.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'php_version'        => array(
						'name'               => __( 'PHP Version Check', 'wpshadow' ),
						'description_short'  => __( 'Verify PHP version meets requirements', 'wpshadow' ),
						'description_long'   => __( 'Validates PHP version against WordPress minimum requirements and recommended versions. Alerts with critical severity if PHP version is below minimum (7.4), warning severity if below recommended (8.1+), and pass status for modern versions. Includes PHP lifecycle information showing end-of-life dates for current PHP version. Critical for security as outdated PHP versions no longer receive security patches. Provides upgrade recommendations and compatibility information. Essential for maintaining secure, performant WordPress installations.', 'wpshadow' ),
						'description_wizard' => __( 'Monitor your PHP version to ensure compatibility and security. Get warnings before your PHP version reaches end-of-life.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'database_health'    => array(
						'name'               => __( 'Database Health', 'wpshadow' ),
						'description_short'  => __( 'Monitor database performance and issues', 'wpshadow' ),
						'description_long'   => __( 'Comprehensive database health monitoring including connection testing, autoloaded data size analysis, expired transient detection, and query performance metrics. Checks for large autoload data that slows page loads (threshold: 1MB+), identifies expired transients cluttering the options table (threshold: 100+), and monitors overall database size. Provides recommendations for optimization including database cleanup, index optimization, and autoload reduction strategies. Critical for site performance as database issues directly impact page load times and user experience.', 'wpshadow' ),
						'description_wizard' => __( 'Monitor your database for performance issues like excessive autoloaded data and expired transients that slow down your site.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'file_permissions'   => array(
						'name'               => __( 'File Permission Check', 'wpshadow' ),
						'description_short'  => __( 'Verify secure file permissions', 'wpshadow' ),
						'description_long'   => __( 'Validates file system permissions for security-critical files and directories. Checks wp-config.php permissions (should be 400 or 440 - not world-readable), verifies wp-content directory is writable for uploads and plugin installations, validates .htaccess protection, and ensures wp-admin directory has appropriate restrictions. Alerts on insecure permissions that could allow unauthorized access or modification. Includes remediation instructions for correcting permission issues via FTP or SSH. Essential security check as improper permissions are common attack vectors.', 'wpshadow' ),
						'description_wizard' => __( 'Check that your WordPress files have secure permissions to prevent unauthorized access or modifications.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'security_headers'   => array(
						'name'               => __( 'Security Headers', 'wpshadow' ),
						'description_short'  => __( 'Check HTTP security headers', 'wpshadow' ),
						'description_long'   => __( 'Analyzes HTTP response headers for security best practices. Checks for X-Frame-Options header (clickjacking protection), X-Content-Type-Options header (MIME sniffing protection), Content-Security-Policy header (XSS protection), Strict-Transport-Security header (HTTPS enforcement), X-XSS-Protection header (legacy XSS protection), and Referrer-Policy header (privacy protection). Makes external request to site homepage to analyze actual headers being sent. Provides severity ratings and recommendations for implementing missing headers. Critical for security as proper headers prevent many common web attacks.', 'wpshadow' ),
						'description_wizard' => __( 'Verify your site sends proper HTTP security headers to protect against clickjacking, XSS, and other attacks.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'debug_mode'         => array(
						'name'               => __( 'Debug Mode Detection', 'wpshadow' ),
						'description_short'  => __( 'Alert if debug mode enabled in production', 'wpshadow' ),
						'description_long'   => __( 'Detects if WP_DEBUG constant is enabled, which should typically be disabled on production sites. Critical severity if WP_DEBUG_DISPLAY is also enabled (publicly displays PHP errors - major security risk), warning severity if only WP_DEBUG is enabled (logs errors but doesn\'t display them), pass status if disabled. Debug mode can expose sensitive information including file paths, database credentials, and internal application structure to attackers. Includes recommendations for proper debug configuration and secure logging practices. Essential production readiness check.', 'wpshadow' ),
						'description_wizard' => __( 'Get alerts if WordPress debug mode is accidentally left enabled on your production site, which could expose sensitive information.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'error_log'          => array(
						'name'               => __( 'Error Log Monitoring', 'wpshadow' ),
						'description_short'  => __( 'Monitor error log for critical issues', 'wpshadow' ),
						'description_long'   => __( 'Monitors PHP error logs for critical errors and file size issues. Checks error_log file size against threshold (default: 10MB) and alerts if log file is growing excessively. Scans last 50KB of log file for fatal errors, warnings, and critical issues. Counts PHP fatal errors in recent logs and alerts if threshold exceeded. Large error logs indicate ongoing problems that need investigation. Includes log rotation recommendations and steps to identify root causes of errors. Critical for maintaining site stability as unchecked errors can lead to site failures.', 'wpshadow' ),
						'description_wizard' => __( 'Monitor your error logs for fatal errors and excessive log file growth that could indicate serious problems.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'ssl_check'          => array(
						'name'               => __( 'SSL/HTTPS Configuration', 'wpshadow' ),
						'description_short'  => __( 'Verify SSL/HTTPS is properly configured', 'wpshadow' ),
						'description_long'   => __( 'Validates SSL/HTTPS configuration and consistency. Checks if site URLs (home_url and site_url) use https:// protocol, verifies is_ssl() detection is working correctly, confirms FORCE_SSL_ADMIN constant is defined for admin security, and detects mixed content issues. Critical severity if URLs are configured for HTTPS but SSL is not detected (indicates configuration problem), warning severity if FORCE_SSL_ADMIN is not defined, info severity if not using HTTPS. Essential for security and SEO as browsers flag non-HTTPS sites and search engines favor HTTPS sites in rankings.', 'wpshadow' ),
						'description_wizard' => __( 'Verify your SSL/HTTPS is properly configured to ensure secure connections and avoid browser security warnings.', 'wpshadow' ),
						'default_enabled'    => true,
					),
				'phpinfo_viewer'     => array(
					'name'               => __( 'PHP Info Viewer', 'wpshadow' ),
					'description_short'  => __( 'View detailed PHP configuration and modules', 'wpshadow' ),
					'description_long'   => __( 'Displays comprehensive PHP information including version, loaded extensions, configuration settings, and system information. Useful for troubleshooting compatibility issues, checking if required PHP extensions are installed, verifying PHP settings match WordPress recommendations, and generating diagnostic reports for support purposes. Shows memory limits, execution time, file upload sizes, and other critical PHP settings that affect WordPress performance and functionality.', 'wpshadow' ),
					'description_wizard' => __( 'View your PHP configuration, installed extensions, and system information for troubleshooting and verification.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'data_retention_policies' => array(
					'name'               => __( 'Data Retention Policies', 'wpshadow' ),
					'description_short'  => __( 'Manage automated data cleanup and retention schedules', 'wpshadow' ),
					'description_long'   => __( 'Configures automated data retention policies for activity logs, privacy requests, diagnostic tokens, error logs, and user sessions. Enables automatic purging of old data based on retention periods you set (defaults: 90 days for activity logs, 180 days for privacy requests, 30 days for diagnostic tokens, 30 days for error logs, 7 days for user sessions). Helps manage database size, improves performance, and ensures compliance with data minimization best practices. Scheduled to run daily and can be manually triggered for immediate cleanup.', 'wpshadow' ),
					'description_wizard' => __( 'Automatically clean up old logs and data to keep your database lean and maintain privacy compliance. Set retention periods for different types of data on your site.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'activity_log_cleanup' => array(
					'name'               => __( 'Activity Log Auto-Cleanup', 'wpshadow' ),
					'description_short'  => __( 'Automatically purge old activity log records', 'wpshadow' ),
					'description_long'   => __( 'Automatically removes activity log records older than the configured retention period (default: 90 days). Keeps your activity log database from growing unbounded while retaining recent activity for audit trail purposes. Works with WPShadow activity logging system to maintain a clean, queryable audit trail without consuming excessive database space.', 'wpshadow' ),
					'description_wizard' => __( 'Keep activity logs from growing too large by automatically removing records older than your specified retention period.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'error_log_cleanup' => array(
					'name'               => __( 'Error Log Auto-Cleanup', 'wpshadow' ),
					'description_short'  => __( 'Automatically rotate and purge old error logs', 'wpshadow' ),
					'description_long'   => __( 'Automatically removes old error log entries beyond the retention period (default: 30 days) to prevent error logs from consuming excessive disk space. Large error logs can cause performance issues and make it difficult to find current problems among old entries. Helps maintain readable, recent error logs while preventing disk space issues. Can also rotate error logs when they exceed size threshold (10MB+).', 'wpshadow' ),
					'description_wizard' => __( 'Prevent error logs from growing too large and causing disk space issues by automatically removing old entries.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'session_cleanup' => array(
					'name'               => __( 'User Session Cleanup', 'wpshadow' ),
					'description_short'  => __( 'Automatically remove expired user sessions', 'wpshadow' ),
					'description_long'   => __( 'Automatically removes old user session data and transients beyond the retention period (default: 7 days). User session data can accumulate in WordPress options/transients and cause performance issues. Helps keep your WordPress options table clean and prevents session data from becoming stale. Works with both standard WordPress session management and any custom session tracking.', 'wpshadow' ),
					'description_wizard' => __( 'Clean up old user session data so your WordPress database stays performant and doesn\'t accumulate stale session records.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				),
			)
		);
	}

	/**
	 * Check if feature has details page.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
	}

	/**
	 * Register feature hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		// Schedule daily diagnostics
		add_action( 'wp', array( $this, 'schedule_diagnostics' ) );
		add_action( 'wpshadow_daily_diagnostics', array( $this, 'run_diagnostics' ) );

		// Admin notices for critical issues
		add_action( 'admin_init', array( $this, 'check_critical_issues' ) );
		add_action( 'admin_notices', array( $this, 'display_diagnostic_notices' ) );

		// AJAX handlers
		add_action( 'wp_ajax_wpshadow_run_diagnostics', array( $this, 'ajax_run_diagnostics' ) );
		add_action( 'wp_ajax_wpshadow_get_phpinfo', array( $this, 'ajax_get_phpinfo' ) );
	}

	/**
	 * Schedule daily diagnostic checks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function schedule_diagnostics(): void {
		if ( ! wp_next_scheduled( 'wpshadow_daily_diagnostics' ) ) {
			wp_schedule_event( time(), 'daily', 'wpshadow_daily_diagnostics' );
		}
	}

	/**
	 * Run full diagnostic checks.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
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
	 * @since 1.0.0
	 * @return array Check result.
	 */
	private function check_core_updates(): array {
		require_once ABSPATH . 'wp-admin/includes/update.php';
		$updates = get_core_updates();
		if ( ! is_array( $updates ) || empty( $updates ) ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'WordPress core is up to date.', 'wpshadow' ),
			);
		}
		$update = reset( $updates );
		if ( 'upgrade' === $update->response ) {
			return array(
				'status'   => 'critical',
				'message'  => sprintf(
					/* translators: %s: version number */
					__( 'WordPress core update available: %s', 'wpshadow' ),
					$update->version
				),
				'severity' => 'high',
			);
		}
		return array(
			'status'  => 'pass',
			'message' => __( 'WordPress core is up to date.', 'wpshadow' ),
		);
	}

	/**
	 * Check PHP version compatibility.
	 *
	 * @since 1.0.0
	 * @return array Check result.
	 */
	private function check_php_version(): array {
		$current_version     = PHP_VERSION;
		$recommended_version = '8.1';
		$minimum_version     = '7.4';
		if ( version_compare( $current_version, $minimum_version, '<' ) ) {
			return array(
				'status'   => 'critical',
				'message'  => sprintf(
					/* translators: 1: current version, 2: minimum version */
					__( 'PHP version %1$s is outdated. Minimum required: %2$s', 'wpshadow' ),
					$current_version,
					$minimum_version
				),
				'severity' => 'critical',
			);
		}
		if ( version_compare( $current_version, $recommended_version, '<' ) ) {
			return array(
				'status'   => 'warning',
				'message'  => sprintf(
					/* translators: 1: current version, 2: recommended version */
					__( 'PHP version %1$s works but %2$s+ is recommended for better performance and security.', 'wpshadow' ),
					$current_version,
					$recommended_version
				),
				'severity' => 'medium',
			);
		}
		return array(
			'status'  => 'pass',
			'message' => sprintf(
				/* translators: %s: PHP version */
				__( 'PHP version %s is up to date.', 'wpshadow' ),
				$current_version
			),
		);
	}

	/**
	 * Check database health.
	 *
	 * @since 1.0.0
	 * @return array Check result.
	 */
	private function check_database_health(): array {
		global $wpdb;
		$issues = array();
		if ( ! $wpdb->check_connection( false ) ) {
			return array(
				'status'   => 'critical',
				'message'  => __( 'Database connection failed.', 'wpshadow' ),
				'severity' => 'critical',
			);
		}
		$autoload_size      = $wpdb->get_var( "SELECT SUM(LENGTH(option_value)) as autoload_size FROM {$wpdb->options} WHERE autoload = 'yes'" );
		$autoload_threshold = $this->get_setting( 'autoload_threshold_mb', 1, false ) * 1048576;
		if ( $autoload_size > $autoload_threshold ) {
			$issues[] = sprintf(
				/* translators: 1: size in MB, 2: threshold in MB */
				__( 'Large autoloaded data: %1$s MB (threshold: %2$s MB)', 'wpshadow' ),
				round( $autoload_size / 1048576, 2 ),
				$this->get_setting( 'autoload_threshold_mb', 1, false )
			);
		}
		$expired_transients  = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d", '%_transient_timeout_%', time() ) );
		$transient_threshold = $this->get_setting( 'transient_cleanup_count', 100, false );
		if ( $expired_transients > $transient_threshold ) {
			$issues[] = sprintf(
				/* translators: 1: count, 2: threshold */
				__( '%1$d expired transients should be cleaned up (threshold: %2$d).', 'wpshadow' ),
				$expired_transients,
				$transient_threshold
			);
		}
		if ( ! empty( $issues ) ) {
			return array(
				'status'   => 'warning',
				'message'  => implode( ' ', $issues ),
				'severity' => 'medium',
			);
		}
		return array(
			'status'  => 'pass',
			'message' => __( 'Database is healthy.', 'wpshadow' ),
		);
	}

	/**
	 * Check file permissions.
	 *
	 * @since 1.0.0
	 * @return array Check result.
	 */
	private function check_file_permissions(): array {
		$issues    = array();
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
			return array(
				'status'   => 'warning',
				'message'  => implode( ' ', $issues ),
				'severity' => 'medium',
			);
		}
		return array(
			'status'  => 'pass',
			'message' => __( 'File permissions are correctly configured.', 'wpshadow' ),
		);
	}

	/**
	 * Check security headers.
	 *
	 * @since 1.0.0
	 * @return array Check result.
	 */
	private function check_security_headers(): array {
		$home_url = home_url();
		$response = wp_remote_head(
			$home_url,
			array(
				'timeout' => 10,
			)
		);
		if ( is_wp_error( $response ) ) {
			return array(
				'status'  => 'warning',
				'message' => __( 'Unable to check security headers.', 'wpshadow' ),
			);
		}
		$headers = wp_remote_retrieve_headers( $response );
		$issues  = array();
		if ( empty( $headers['x-frame-options'] ) ) {
			$issues[] = __( 'X-Frame-Options header missing (clickjacking protection).', 'wpshadow' );
		}
		if ( empty( $headers['x-content-type-options'] ) ) {
			$issues[] = __( 'X-Content-Type-Options header missing (MIME sniffing protection).', 'wpshadow' );
		}
		if ( ! empty( $issues ) ) {
			return array(
				'status'   => 'warning',
				'message'  => implode( ' ', $issues ),
				'severity' => 'low',
			);
		}
		return array(
			'status'  => 'pass',
			'message' => __( 'Security headers are properly configured.', 'wpshadow' ),
		);
	}

	/**
	 * Check if debug mode is enabled in production.
	 *
	 * @since 1.0.0
	 * @return array Check result.
	 */
	private function check_debug_mode(): array {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$message = __( 'WP_DEBUG is enabled.', 'wpshadow' );
			if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
				$message .= ' ' . __( 'Errors are being displayed publicly (security risk).', 'wpshadow' );
				return array(
					'status'   => 'critical',
					'message'  => $message,
					'severity' => 'high',
				);
			}
			return array(
				'status'   => 'warning',
				'message'  => $message . ' ' . __( 'Consider disabling in production.', 'wpshadow' ),
				'severity' => 'medium',
			);
		}
		return array(
			'status'  => 'pass',
			'message' => __( 'Debug mode is not enabled.', 'wpshadow' ),
		);
	}

	/**
	 * Check error log for recent critical errors.
	 *
	 * @since 1.0.0
	 * @return array Check result.
	 */
	private function check_error_log(): array {
		$log_file = ini_get( 'error_log' );
		if ( empty( $log_file ) || ! file_exists( $log_file ) ) {
			$log_file = WP_CONTENT_DIR . '/debug.log';
		}
		if ( ! file_exists( $log_file ) || ! is_readable( $log_file ) ) {
			return array(
				'status'  => 'info',
				'message' => __( 'No error log file found or not readable.', 'wpshadow' ),
			);
		}
		$log_size       = filesize( $log_file );
		$log_threshold  = $this->get_setting( 'log_size_threshold_mb', 10, false ) * 1048576;
		if ( $log_size > $log_threshold ) {
			return array(
				'status'   => 'warning',
				'message'  => sprintf(
					/* translators: 1: size in MB, 2: threshold in MB */
					__( 'Error log is large (%1$s MB) and should be reviewed/cleared (threshold: %2$s MB).', 'wpshadow' ),
					round( $log_size / 1048576, 2 ),
					$this->get_setting( 'log_size_threshold_mb', 10, false )
				),
				'severity' => 'medium',
			);
		}
		$handle = fopen( $log_file, 'r' );
		if ( false === $handle ) {
			return array(
				'status'  => 'info',
				'message' => __( 'Unable to read error log.', 'wpshadow' ),
			);
		}
		fseek( $handle, max( 0, $log_size - 51200 ), SEEK_SET );
		$content = fread( $handle, 51200 );
		fclose( $handle );
		$fatal_count = substr_count( $content, 'Fatal error' ) + substr_count( $content, 'PHP Fatal error' );
		if ( $fatal_count > 0 ) {
			return array(
				'status'   => 'critical',
				'message'  => sprintf(
					/* translators: %d: number of errors */
					__( '%d fatal error(s) detected in recent logs.', 'wpshadow' ),
					$fatal_count
				),
				'severity' => 'high',
			);
		}
		return array(
			'status'  => 'pass',
			'message' => __( 'No critical errors in recent logs.', 'wpshadow' ),
		);
	}

	/**
	 * Check SSL/HTTPS configuration.
	 *
	 * @since 1.0.0
	 * @return array Check result.
	 */
	private function check_ssl_configuration(): array {
		$home_url = home_url();
		$site_url = site_url();
		if ( ! is_ssl() && ( strpos( $home_url, 'https://' ) === 0 || strpos( $site_url, 'https://' ) === 0 ) ) {
			return array(
				'status'   => 'critical',
				'message'  => __( 'Site URLs are configured for HTTPS but SSL is not detected.', 'wpshadow' ),
				'severity' => 'high',
			);
		}
		if ( ! defined( 'FORCE_SSL_ADMIN' ) ) {
			return array(
				'status'   => 'warning',
				'message'  => __( 'FORCE_SSL_ADMIN is not defined. Consider forcing SSL for admin area.', 'wpshadow' ),
				'severity' => 'low',
			);
		}
		if ( is_ssl() ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'SSL/HTTPS is properly configured.', 'wpshadow' ),
			);
		}
		return array(
			'status'   => 'info',
			'message'  => __( 'Site is not using HTTPS. Consider enabling SSL for security.', 'wpshadow' ),
			'severity' => 'low',
		);
	}

	/**
	 * Log diagnostic issues found.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
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
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_run_diagnostics(): void {
		check_ajax_referer( 'wpshadow_diagnostics', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}
		$results = $this->run_diagnostics();
		wp_send_json_success(
			array(
				'results' => $results,
				'message' => __( 'Diagnostics completed successfully.', 'wpshadow' ),
			)
		);
	}

	/**
	 * AJAX handler to retrieve PHP info.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_get_phpinfo(): void {
		check_ajax_referer( 'wpshadow_diagnostics', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		if ( ! $this->is_sub_feature_enabled( 'phpinfo_viewer' ) ) {
			wp_send_json_error( array( 'message' => __( 'PHP Info Viewer is not enabled.', 'wpshadow' ) ) );
		}

		// Collect PHP information
		$php_info = array(
			'version'              => phpversion(),
			'sapi'                 => php_sapi_name(),
			'os'                   => php_uname(),
			'memory_limit'         => ini_get( 'memory_limit' ),
			'max_execution_time'   => (int) ini_get( 'max_execution_time' ),
			'max_input_time'       => (int) ini_get( 'max_input_time' ),
			'upload_max_filesize'  => ini_get( 'upload_max_filesize' ),
			'post_max_size'        => ini_get( 'post_max_size' ),
			'default_charset'      => ini_get( 'default_charset' ),
			'date_timezone'        => ini_get( 'date.timezone' ),
			'display_errors'       => ini_get( 'display_errors' ),
			'error_reporting'      => (int) ini_get( 'error_reporting' ),
			'extensions'           => get_loaded_extensions(),
			'extensions_count'     => count( get_loaded_extensions() ),
		);

		// Get specific extensions status
		$required_extensions = array(
			'curl'        => __( 'cURL (HTTP requests)', 'wpshadow' ),
			'gd'          => __( 'GD (image processing)', 'wpshadow' ),
			'json'        => __( 'JSON (data format)', 'wpshadow' ),
			'mbstring'    => __( 'Multibyte String (Unicode support)', 'wpshadow' ),
			'pdo'         => __( 'PDO (database access)', 'wpshadow' ),
			'mysql'       => __( 'MySQL (legacy database support)', 'wpshadow' ),
			'mysqli'      => __( 'MySQLi (modern database support)', 'wpshadow' ),
			'openssl'     => __( 'OpenSSL (secure connections)', 'wpshadow' ),
			'zip'         => __( 'ZIP (file compression)', 'wpshadow' ),
		);

		$extensions_status = array();
		foreach ( $required_extensions as $ext => $label ) {
			$extensions_status[ $ext ] = array(
				'label'     => $label,
				'installed' => extension_loaded( $ext ),
			);
		}

		wp_send_json_success(
			array(
				'php_info'              => $php_info,
				'extensions_status'     => $extensions_status,
				'message'               => __( 'PHP information retrieved successfully.', 'wpshadow' ),
			)
		);
	}

	/**
	 * Cleanup on disable.
	 *
	 * @since 1.0.0
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
