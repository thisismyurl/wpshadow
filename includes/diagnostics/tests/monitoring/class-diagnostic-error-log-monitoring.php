<?php
/**
 * Error Log Monitoring Diagnostic
 *
 * Analyzes error logging configuration and recent errors.
 *
 * @since   1.6033.2140
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Error Log Monitoring Diagnostic
 *
 * Evaluates error logging and identifies recent critical errors.
 *
 * @since 1.6033.2140
 */
class Diagnostic_Error_Log_Monitoring extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'error-log-monitoring';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Error Log Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes error logging configuration and recent errors';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2140
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if error logging is enabled
		$log_errors = ini_get( 'log_errors' );
		$display_errors = ini_get( 'display_errors' );
		$error_log_path = ini_get( 'error_log' );

		// Check WP_DEBUG settings
		$wp_debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$wp_debug_log = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
		$wp_debug_display = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;

		// Try to locate error log
		$log_files = array(
			WP_CONTENT_DIR . '/debug.log',
			ABSPATH . 'error_log',
			ABSPATH . 'error.log',
			$error_log_path,
		);

		$found_log = null;
		$log_size = 0;
		$recent_errors = 0;

		foreach ( $log_files as $log_file ) {
			if ( empty( $log_file ) || ! file_exists( $log_file ) ) {
				continue;
			}

			$found_log = $log_file;
			$log_size = filesize( $log_file );

			// Check last 100 lines for recent errors (last 24 hours)
			if ( is_readable( $log_file ) ) {
				$handle = fopen( $log_file, 'r' );
				if ( $handle ) {
					// Read last 50KB for recent entries
					fseek( $handle, max( 0, $log_size - 51200 ), SEEK_SET );
					$content = fread( $handle, 51200 );
					fclose( $handle );

					// Count error occurrences
					$recent_errors = substr_count( $content, '[' . gmdate( 'd-M-Y' ) . ']' );
				}
			}

			break;
		}

		// Convert log size to MB
		$log_size_mb = round( $log_size / 1048576, 2 );

		// Generate findings based on configuration
		if ( ! $log_errors && ! $wp_debug_log ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Error logging not enabled. Enable error logging to track and debug issues proactively.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/error-log-monitoring',
				'meta'         => array(
					'log_errors'       => $log_errors,
					'wp_debug'         => $wp_debug,
					'wp_debug_log'     => $wp_debug_log,
					'wp_debug_display' => $wp_debug_display,
					'recommendation'   => 'Enable WP_DEBUG_LOG in wp-config.php',
					'wp_config_code'   => "define('WP_DEBUG', true);\ndefine('WP_DEBUG_LOG', true);\ndefine('WP_DEBUG_DISPLAY', false);",
				),
			);
		}

		// Alert on large error log
		if ( $log_size_mb > 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: log file size in MB */
					__( 'Error log file is %s MB. Large log files indicate recurring errors that need attention.', 'wpshadow' ),
					$log_size_mb
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/error-log-monitoring',
				'meta'         => array(
					'log_file'         => basename( $found_log ),
					'log_size_mb'      => $log_size_mb,
					'recent_errors'    => $recent_errors,
					'recommendation'   => 'Review and fix recurring errors, then clear log file',
					'impact'           => 'Large logs slow file operations and fill disk space',
				),
			);
		}

		// Alert on high error rate
		if ( $recent_errors > 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of recent errors */
					__( '%d errors logged today. High error rate may indicate site issues requiring immediate attention.', 'wpshadow' ),
					$recent_errors
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/error-log-monitoring',
				'meta'         => array(
					'recent_errors'  => $recent_errors,
					'log_file'       => basename( $found_log ),
					'recommendation' => 'Review error log and fix recurring issues',
				),
			);
		}

		// Warning if debug display enabled on production
		if ( $wp_debug_display && ! $this->is_local_environment() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WP_DEBUG_DISPLAY enabled on production site. This exposes error details to visitors and poses security risk.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/error-log-monitoring',
				'meta'         => array(
					'wp_debug_display' => $wp_debug_display,
					'recommendation'   => 'Set WP_DEBUG_DISPLAY to false in wp-config.php',
					'security_risk'    => 'Exposes file paths and database details to attackers',
				),
			);
		}

		return null;
	}

	/**
	 * Check if environment is local development.
	 *
	 * @since  1.6033.2140
	 * @return bool True if local environment.
	 */
	private function is_local_environment() {
		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		return strpos( $host, 'localhost' ) !== false ||
		       strpos( $host, '.local' ) !== false ||
		       strpos( $host, '127.0.0.1' ) !== false;
	}
}
