<?php
/**
 * Global Error Handler Not Implemented Diagnostic
 *
 * Checks if global error handler is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Global Error Handler Not Implemented Diagnostic Class
 *
 * Detects missing global error handler.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Global_Error_Handler_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'global-error-handler-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Global Error Handler Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if global error handler is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if WP_DEBUG is enabled.
		$wp_debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$wp_debug_log = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
		$wp_debug_display = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;

		// Check for error monitoring plugins/services.
		$error_plugins = array(
			'query-monitor/query-monitor.php'    => 'Query Monitor',
			'debug-bar/debug-bar.php'            => 'Debug Bar',
			'wp-log-viewer/wp-log-viewer.php'    => 'WP Log Viewer',
		);

		$plugin_detected = false;
		$plugin_name     = '';

		foreach ( $error_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$plugin_detected = true;
				$plugin_name     = $name;
				break;
			}
		}

		// Check for fatal error handler (WP 5.2+).
		global $wp_version;
		$has_recovery_mode = version_compare( $wp_version, '5.2.0', '>=' );

		// Check for custom error handlers.
		$has_custom_handler = has_action( 'shutdown' ) || has_filter( 'wp_php_error_message' );

		// Check if error logging is configured.
		$error_log_configured = $wp_debug_log || ini_get( 'log_errors' );

		// Production site without error logging.
		if ( ! $wp_debug && ! $error_log_configured && ! $plugin_detected && ! $has_custom_handler ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Global error handler not implemented. Errors and warnings may go unnoticed in production. Enable WP_DEBUG_LOG in wp-config.php to log errors without displaying them to visitors, or install Query Monitor for development.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/error-logging',
				'details'     => array(
					'wp_debug'             => false,
					'wp_debug_log'         => false,
					'wp_debug_display'     => $wp_debug_display,
					'has_recovery_mode'    => $has_recovery_mode,
					'plugin_detected'      => false,
					'recommendation'       => __( 'Add to wp-config.php: define("WP_DEBUG", true); define("WP_DEBUG_LOG", true); define("WP_DEBUG_DISPLAY", false); This logs errors to wp-content/debug.log without showing them to visitors.', 'wpshadow' ),
					'benefits'             => array(
						'detect_issues' => 'Catch errors before users report them',
						'debugging'     => 'Easier to troubleshoot problems',
						'monitoring'    => 'Track error patterns and frequency',
						'security'      => 'Detect security-related errors',
					),
					'wp_config_example'    => "define('WP_DEBUG', true);\ndefine('WP_DEBUG_LOG', true);\ndefine('WP_DEBUG_DISPLAY', false);",
				),
			);
		}

		// Development site with errors displayed to public.
		if ( $wp_debug && $wp_debug_display && ! is_local_environment() ) {
			return array(
				'id'          => self::$slug,
				'title'       => __( 'Debug Display Enabled in Production', 'wpshadow' ),
				'description' => __( 'WP_DEBUG_DISPLAY is enabled on production site. Errors are visible to all visitors, exposing server paths and potentially sensitive information. Set WP_DEBUG_DISPLAY to false and use WP_DEBUG_LOG instead.', 'wpshadow' ),
				'severity'    => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/error-logging',
				'details'     => array(
					'wp_debug_display' => true,
					'security_risk'    => 'Exposes server paths, database errors, plugin vulnerabilities',
					'recommendation'   => __( 'URGENT: Set define("WP_DEBUG_DISPLAY", false); in wp-config.php immediately.', 'wpshadow' ),
				),
			);
		}

		// No issues - error handling configured.
		return null;
	}

	/**
	 * Check if site is in local development environment.
	 *
	 * @since  1.6030.2352
	 * @return bool True if local environment.
	 */
	private static function is_local_environment() {
		$server_name = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
		return in_array( $server_name, array( 'localhost', '127.0.0.1', '::1' ), true ) ||
		       strpos( $server_name, '.local' ) !== false ||
		       strpos( $server_name, '.test' ) !== false;
	}
}
