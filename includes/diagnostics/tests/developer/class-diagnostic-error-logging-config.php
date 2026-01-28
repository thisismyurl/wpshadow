<?php
/**
 * Error Logging Configuration Diagnostic
 *
 * Verifies PHP error logging is properly configured to capture and
 * store errors for troubleshooting without exposing them publicly.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Error_Logging_Config Class
 *
 * Monitors error logging configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Error_Logging_Config extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'error-logging-config';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Error Logging Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies PHP error logging is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if misconfigured, null otherwise.
	 */
	public static function check() {
		$logging_check = self::check_error_logging();

		if ( ! $logging_check['has_issue'] ) {
			return null; // Logging configured properly
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'PHP error logging not properly configured. Errors disappear = can\'t troubleshoot. Silent failures = site breaks without knowing why.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/error-logging',
			'family'       => self::$family,
			'meta'         => array(
				'log_errors'     => $logging_check['log_errors'],
				'display_errors' => $logging_check['display_errors'],
				'error_log_path' => $logging_check['error_log_path'],
			),
			'details'      => array(
				'why_error_logging_matters' => array(
					__( 'Catch bugs before users report them' ),
					__( 'Understand why features break' ),
					__( 'Track down intermittent issues' ),
					__( 'Monitor plugin/theme conflicts' ),
					__( 'Identify performance bottlenecks' ),
				),
				'php_error_settings'        => array(
					'log_errors' => array(
						'Purpose: Enable error logging',
						'Production: On',
						'php.ini: log_errors = On',
					),
					'display_errors' => array(
						'Purpose: Show errors on screen',
						'Production: Off (security risk)',
						'php.ini: display_errors = Off',
					),
					'error_reporting' => array(
						'Purpose: What level of errors to log',
						'Production: E_ALL & ~E_DEPRECATED',
						'Logs: Errors, warnings, notices (not deprecations)',
					),
					'error_log' => array(
						'Purpose: Where to save log file',
						'Default: /var/log/php_errors.log',
						'Custom: /home/user/logs/php-errors.log',
					),
				),
				'configuring_via_php_ini'   => array(
					'System php.ini' => array(
						'Location: /etc/php/8.1/apache2/php.ini (Linux)',
						'Edit requires root access',
						'Affects all sites on server',
					),
					'User php.ini' => array(
						'Location: /home/user/public_html/php.ini',
						'Shared hosting friendly',
						'Site-specific settings',
					),
					'Required Settings' => array(
						'log_errors = On',
						'display_errors = Off',
						'error_log = /path/to/php-errors.log',
						'error_reporting = E_ALL & ~E_DEPRECATED',
					),
				),
				'configuring_via_htaccess'  => array(
					'.htaccess Method' => array(
						'File: /public_html/.htaccess',
						'Add:',
						'php_flag log_errors On',
						'php_flag display_errors Off',
						'php_value error_log /home/user/logs/php-errors.log',
					),
					'Shared Hosting Compatible' => 'Most hosts allow .htaccess PHP config',
				),
				'monitoring_error_logs'     => array(
					'Via SSH' => array(
						'View: tail -f /var/log/php_errors.log',
						'Search: grep "Fatal error" /var/log/php_errors.log',
					),
					'Via cPanel' => array(
						'cPanel → Metrics → Errors',
						'Shows recent PHP errors',
					),
					'Via Plugin' => array(
						'Error Log Monitor (free)',
						'WP Dashboard → Error Log',
						'Email alerts on critical errors',
					),
				),
				'log_rotation'              => array(
					__( 'Logs grow large over time (100MB+)' ),
					__( 'Set up logrotate to compress old logs' ),
					__( 'Keep last 7-14 days of detailed logs' ),
					__( 'Archive monthly logs to storage' ),
				),
			),
		);
	}

	/**
	 * Check error logging configuration.
	 *
	 * @since  1.2601.2148
	 * @return array Error logging status.
	 */
	private static function check_error_logging() {
		$log_errors = (bool) ini_get( 'log_errors' );
		$display_errors = (bool) ini_get( 'display_errors' );
		$error_log_path = ini_get( 'error_log' );

		// Issue if logging is disabled
		$has_issue = ! $log_errors;

		// Also issue if production site displaying errors
		$home_url = home_url();
		$is_production = strpos( $home_url, 'localhost' ) === false && strpos( $home_url, '127.0.0.1' ) === false;
		if ( $is_production && $display_errors ) {
			$has_issue = true;
		}

		return array(
			'has_issue'      => $has_issue,
			'log_errors'     => $log_errors ? 'enabled' : 'disabled',
			'display_errors' => $display_errors ? 'enabled' : 'disabled',
			'error_log_path' => $error_log_path ?: 'not set',
		);
	}
}
