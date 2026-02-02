<?php
/**
 * Silent Tool Failures Without Logs
 *
 * Checks for comprehensive error logging in tool operations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tools
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Silent_Tool_Failures_Without_Logs Class
 *
 * Validates comprehensive error logging for tool operations.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Silent_Tool_Failures_Without_Logs extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'silent-tool-failures-no-logs';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tool Operation Logging';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates comprehensive error logging for tool operation failures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests logging comprehensiveness for tool failures.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check for tool operation logging
		if ( ! self::has_tool_logging() ) {
			$issues[] = __( 'Tool operations not logged to debug.log', 'wpshadow' );
		}

		// 2. Check for error capture
		if ( ! self::captures_error_details() ) {
			$issues[] = __( 'Tool error details not captured (error type, stack trace, context)', 'wpshadow' );
		}

		// 3. Check for warning logging
		if ( ! self::logs_warnings() ) {
			$issues[] = __( 'Warnings not logged (recoverable issues, degraded operations)', 'wpshadow' );
		}

		// 4. Check for admin logging
		if ( ! self::logs_to_admin_notices() ) {
			$issues[] = __( 'Errors not displayed in admin notices', 'wpshadow' );
		}

		// 5. Check for activity logging
		if ( ! self::logs_to_activity() ) {
			$issues[] = __( 'Tool operations not logged to activity tracker', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of logging issues */
					__( '%d logging issues found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/tool-operation-logging',
				'recommendations' => array(
					__( 'Log all tool operations to debug.log', 'wpshadow' ),
					__( 'Capture full error details (type, message, stack trace)', 'wpshadow' ),
					__( 'Log warnings for degraded operations', 'wpshadow' ),
					__( 'Display errors in admin notices for visibility', 'wpshadow' ),
					__( 'Log to activity tracker for audit trail', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for tool operation logging.
	 *
	 * @since  1.2601.2148
	 * @return bool True if logging implemented.
	 */
	private static function has_tool_logging() {
		// Check for WP_DEBUG configuration
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return false;
		}

		// Check for WP_DEBUG_LOG
		if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
			return false;
		}

		// Check if debug.log can be written
		$debug_log = wp_upload_dir();
		$debug_log = $debug_log['basedir'] . '/debug.log';

		if ( ! is_writable( dirname( $debug_log ) ) ) {
			return false;
		}

		// Check for logging hook
		if ( has_filter( 'wpshadow_log_tool_operation' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for error capture.
	 *
	 * @since  1.2601.2148
	 * @return bool True if error details captured.
	 */
	private static function captures_error_details() {
		// Check for comprehensive error handler
		if ( has_filter( 'wpshadow_capture_error_details' ) ) {
			return true;
		}

		// Check for stack trace logging
		if ( has_filter( 'wpshadow_log_stack_trace' ) ) {
			return true;
		}

		// Check for error context logging
		if ( has_filter( 'wpshadow_log_error_context' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for warning logging.
	 *
	 * @since  1.2601.2148
	 * @return bool True if warnings logged.
	 */
	private static function logs_warnings() {
		// Check for warning logging
		if ( has_filter( 'wpshadow_log_warning' ) ) {
			return true;
		}

		// Check for degraded operation detection
		if ( has_filter( 'wpshadow_detect_degraded_operation' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for admin logging.
	 *
	 * @since  1.2601.2148
	 * @return bool True if admin notices implemented.
	 */
	private static function logs_to_admin_notices() {
		// Check for admin notice generation
		if ( has_filter( 'wpshadow_show_tool_error_notice' ) ) {
			return true;
		}

		// Check for AJAX error response handler
		if ( has_filter( 'wpshadow_ajax_error_response' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for activity logging.
	 *
	 * @since  1.2601.2148
	 * @return bool True if activity logging implemented.
	 */
	private static function logs_to_activity() {
		// Check for activity logger
		if ( class_exists( 'WPShadow\Core\Activity_Logger' ) ) {
			return true;
		}

		// Check for activity tracking hook
		if ( has_filter( 'wpshadow_log_to_activity' ) ) {
			return true;
		}

		return false;
	}
}
