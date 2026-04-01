<?php
/**
 * Silent Failures in Tool Operations Diagnostic
 *
 * Tests for failure logging and reporting.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Silent Failures in Tool Operations Diagnostic Class
 *
 * Tests for failure logging and reporting in tool operations.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Silent_Failures_In_Tool_Operations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'silent-failures-in-tool-operations';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Silent Failures in Tool Operations';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for failure logging and reporting';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for WP_DEBUG_LOG.
		if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
			$issues[] = __( 'WP_DEBUG_LOG not enabled - errors may not be logged', 'wpshadow' );
		}

		// Check for debug.log existence and size.
		$debug_log = WP_CONTENT_DIR . '/debug.log';

		if ( ! file_exists( $debug_log ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$issues[] = __( 'WP_DEBUG enabled but debug.log not found', 'wpshadow' );
			}
		} else {
			// Check log size.
			$log_size = filesize( $debug_log );
			if ( $log_size > 104857600 ) { // > 100MB
				$issues[] = sprintf(
					/* translators: %s: log file size */
					__( 'debug.log is very large (%s) - may slow down site', 'wpshadow' ),
					size_format( $log_size )
				);
			}
		}

		// Check for error reporting level.
		$error_reporting = error_reporting();
		$needed_errors = E_ERROR | E_WARNING | E_PARSE;

		if ( ( $error_reporting & $needed_errors ) !== $needed_errors ) {
			$issues[] = __( 'Error reporting level too low - errors may not be caught', 'wpshadow' );
		}

		// Check for exception handling.
		if ( ! has_action( 'wpshadow_operation_exception' ) ) {
			$issues[] = __( 'No exception handler registered for tool operations', 'wpshadow' );
		}

		// Check for error log monitoring.
		if ( ! function_exists( 'error_log' ) ) {
			$issues[] = __( 'error_log() function not available', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/silent-failures-in-tool-operations?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
