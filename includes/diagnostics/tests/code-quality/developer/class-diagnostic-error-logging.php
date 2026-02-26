<?php
/**
 * Error Logging Diagnostic
 *
 * Checks if proper error logging is configured for debugging.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Error Logging Diagnostic Class
 *
 * Verifies that proper error logging is configured for debugging
 * and monitoring production issues.
 *
 * @since 1.6035.1300
 */
class Diagnostic_Error_Logging extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'error-logging';

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
	protected static $description = 'Checks if proper error logging is configured for debugging';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the error logging diagnostic check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if logging issues detected, null otherwise.
	 */
	public static function check() {
		$issues   = array();
		$warnings = array();
		$config   = array();

		// Check WP_DEBUG.
		$wp_debug           = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$config['WP_DEBUG'] = $wp_debug;

		// Check WP_DEBUG_LOG.
		$wp_debug_log           = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
		$config['WP_DEBUG_LOG'] = $wp_debug_log;

		// Check WP_DEBUG_DISPLAY.
		$wp_debug_display           = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;
		$config['WP_DEBUG_DISPLAY'] = $wp_debug_display;

		// Check SCRIPT_DEBUG.
		$script_debug           = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		$config['SCRIPT_DEBUG'] = $script_debug;

		// Determine environment (production vs development).
		$is_local = in_array( $_SERVER['HTTP_HOST'] ?? '', array( 'localhost', '127.0.0.1', '::1' ), true ) ||
					strpos( $_SERVER['HTTP_HOST'] ?? '', '.local' ) !== false ||
					strpos( $_SERVER['HTTP_HOST'] ?? '', '.test' ) !== false;

		$config['is_local'] = $is_local;

		// Production environment checks.
		if ( ! $is_local ) {
			// WP_DEBUG should be off or logging only.
			if ( $wp_debug && $wp_debug_display ) {
				$issues[] = __( 'WP_DEBUG_DISPLAY is enabled on production - security risk', 'wpshadow' );
			}

			// Error logging should be enabled.
			if ( ! $wp_debug_log ) {
				$warnings[] = __( 'WP_DEBUG_LOG disabled - errors not being logged', 'wpshadow' );
			}

			// Check if debug.log exists and is accessible.
			$debug_log = WP_CONTENT_DIR . '/debug.log';
			if ( file_exists( $debug_log ) ) {
				$config['debug_log_exists'] = true;
				$config['debug_log_size']   = filesize( $debug_log );

				// Check if log is publicly accessible.
				$debug_log_url = content_url( '/debug.log' );
				$response      = wp_remote_head(
					$debug_log_url,
					array(
						'timeout'   => 5,
						'sslverify' => false,
					)
				);

				if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
					$issues[] = __( 'debug.log is publicly accessible - security risk', 'wpshadow' );
				}

				// Warn if log is very large.
				if ( $config['debug_log_size'] > 10 * 1024 * 1024 ) { // 10MB.
					$warnings[] = sprintf(
						/* translators: %s: file size */
						__( 'debug.log is very large (%s) - consider rotation', 'wpshadow' ),
						size_format( $config['debug_log_size'] )
					);
				}
			} else {
				$config['debug_log_exists'] = false;
			}
		} else {
			// Development environment checks.
			if ( ! $wp_debug ) {
				$warnings[] = __( 'WP_DEBUG disabled in development - enable for debugging', 'wpshadow' );
			}

			if ( ! $wp_debug_log ) {
				$warnings[] = __( 'WP_DEBUG_LOG disabled - consider enabling for persistent logs', 'wpshadow' );
			}
		}

		// Check PHP error logging.
		$php_error_log           = ini_get( 'error_log' );
		$config['php_error_log'] = $php_error_log;

		if ( empty( $php_error_log ) || 'syslog' === $php_error_log ) {
			$warnings[] = __( 'PHP error_log not configured to file', 'wpshadow' );
		}

		// Check error reporting level.
		$error_reporting           = error_reporting();
		$config['error_reporting'] = $error_reporting;

		if ( ! $is_local && E_ALL === $error_reporting ) {
			$warnings[] = __( 'Error reporting set to E_ALL in production - consider reducing', 'wpshadow' );
		}

		// Check display_errors.
		$display_errors           = ini_get( 'display_errors' );
		$config['display_errors'] = $display_errors;

		if ( ! $is_local && '1' === $display_errors ) {
			$issues[] = __( 'PHP display_errors enabled in production - security risk', 'wpshadow' );
		}

		// Check log_errors.
		$log_errors           = ini_get( 'log_errors' );
		$config['log_errors'] = $log_errors;

		if ( '1' !== $log_errors ) {
			$warnings[] = __( 'PHP log_errors disabled - errors not being logged', 'wpshadow' );
		}

		// Check for error monitoring services.
		$error_monitoring_plugins = array(
			'bugsnag/bugsnag.php',
			'rollbar/rollbar.php',
			'sentry/sentry.php',
		);

		$has_error_monitoring = false;
		foreach ( $error_monitoring_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_error_monitoring       = true;
				$config['error_monitoring'] = dirname( $plugin );
				break;
			}
		}

		if ( ! $is_local && ! $has_error_monitoring ) {
			$warnings[] = __( 'No error monitoring service detected (Sentry, Rollbar, etc.)', 'wpshadow' );
		}

		// Check wp-config.php for logging constants.
		$wp_config_file = ABSPATH . 'wp-config.php';
		if ( file_exists( $wp_config_file ) && is_readable( $wp_config_file ) ) {
			$wp_config_content = file_get_contents( $wp_config_file );

			// Check if constants are defined in wp-config.
			$has_debug_config           = strpos( $wp_config_content, 'WP_DEBUG' ) !== false;
			$config['has_debug_config'] = $has_debug_config;

			if ( ! $has_debug_config ) {
				$warnings[] = __( 'Debug constants not defined in wp-config.php', 'wpshadow' );
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Error logging has critical security issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/error-logging',
				'context'      => array(
					'is_local'             => $is_local,
					'config'               => $config,
					'has_error_monitoring' => $has_error_monitoring,
					'issues'               => $issues,
					'warnings'             => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Error logging has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/error-logging',
				'context'      => array(
					'is_local'             => $is_local,
					'config'               => $config,
					'has_error_monitoring' => $has_error_monitoring,
					'warnings'             => $warnings,
				),
			);
		}

		return null; // Error logging is properly configured.
	}
}
