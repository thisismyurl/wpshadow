<?php
/**
 * Upload Timeout Errors Diagnostic
 *
 * Monitors for upload timeouts during large file uploads. Tests max_execution_time
 * and max_input_time settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upload Timeout Errors Diagnostic Class
 *
 * Checks for timeout issues during file uploads.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Upload_Timeout_Errors extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'upload-timeout-errors';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Upload Timeout Errors';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates max_execution_time and max_input_time for file uploads';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'uploads';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get PHP timeout settings.
		$max_execution_time = ini_get( 'max_execution_time' );
		$max_input_time = ini_get( 'max_input_time' );
		$default_socket_timeout = ini_get( 'default_socket_timeout' );

		// Check max_execution_time.
		if ( '0' !== $max_execution_time && (int) $max_execution_time > 0 ) {
			if ( (int) $max_execution_time < 30 ) {
				$issues[] = sprintf(
					/* translators: %s: max execution time */
					__( 'max_execution_time is %s seconds (too short, uploads will timeout)', 'wpshadow' ),
					$max_execution_time
				);
			} elseif ( (int) $max_execution_time < 60 ) {
				$issues[] = sprintf(
					/* translators: %s: max execution time */
					__( 'max_execution_time is %s seconds (may timeout on large uploads)', 'wpshadow' ),
					$max_execution_time
				);
			}
		}

		// Check max_input_time.
		if ( '-1' !== $max_input_time && (int) $max_input_time > 0 ) {
			if ( (int) $max_input_time < 60 ) {
				$issues[] = sprintf(
					/* translators: %s: max input time */
					__( 'max_input_time is %s seconds (uploads may timeout during processing)', 'wpshadow' ),
					$max_input_time
				);
			}
		}

		// Check if max_input_time is lower than max_execution_time.
		if ( $max_input_time && $max_execution_time && 
		     '0' !== $max_execution_time && '-1' !== $max_input_time &&
		     (int) $max_input_time < (int) $max_execution_time ) {
			$issues[] = sprintf(
				/* translators: 1: max_input_time, 2: max_execution_time */
				__( 'max_input_time (%1$ss) is less than max_execution_time (%2$ss) (uploads may fail)', 'wpshadow' ),
				$max_input_time,
				$max_execution_time
			);
		}

		// Get upload_max_filesize for context.
		$upload_max_filesize = ini_get( 'upload_max_filesize' );
		$upload_max_bytes = wp_convert_hr_to_bytes( $upload_max_filesize );

		// Estimate upload time based on file size and typical speeds.
		// Assume 1MB/s upload speed (conservative).
		$estimated_seconds = $upload_max_bytes / 1048576;

		if ( '0' !== $max_execution_time && (int) $max_execution_time > 0 ) {
			if ( $estimated_seconds > ( (int) $max_execution_time * 0.8 ) ) {
				$issues[] = sprintf(
					/* translators: 1: upload max filesize, 2: max execution time */
					__( 'Max file size (%1$s) may timeout with max_execution_time of %2$ss', 'wpshadow' ),
					$upload_max_filesize,
					$max_execution_time
				);
			}
		}

		// Check default_socket_timeout.
		if ( $default_socket_timeout && (int) $default_socket_timeout < 60 ) {
			$issues[] = sprintf(
				/* translators: %s: socket timeout */
				__( 'default_socket_timeout is %s seconds (external requests may timeout)', 'wpshadow' ),
				$default_socket_timeout
			);
		}

		// Check if set_time_limit is disabled.
		$disabled_functions = ini_get( 'disable_functions' );
		if ( $disabled_functions && strpos( $disabled_functions, 'set_time_limit' ) !== false ) {
			$issues[] = __( 'set_time_limit() is disabled (cannot extend execution time for uploads)', 'wpshadow' );
		}

		// Check WP_TIMEOUT constant.
		if ( defined( 'WP_TIMEOUT' ) && WP_TIMEOUT < 60 ) {
			$issues[] = sprintf(
				/* translators: %d: WP_TIMEOUT value */
				__( 'WP_TIMEOUT constant is %d seconds (HTTP requests may timeout)', 'wpshadow' ),
				WP_TIMEOUT
			);
		}

		// Check for ignore_user_abort setting.
		$ignore_user_abort = ini_get( 'ignore_user_abort' );
		if ( ! $ignore_user_abort || '0' === $ignore_user_abort ) {
			$issues[] = __( 'ignore_user_abort is disabled (uploads may fail if user closes browser)', 'wpshadow' );
		}

		// Check for output_buffering that might interfere.
		$output_buffering = ini_get( 'output_buffering' );
		if ( $output_buffering && 'off' !== strtolower( $output_buffering ) ) {
			$buffer_size = is_numeric( $output_buffering ) ? (int) $output_buffering : 4096;
			
			if ( $buffer_size < 4096 ) {
				$issues[] = sprintf(
					/* translators: %d: buffer size */
					__( 'output_buffering is %d bytes (very small, may cause upload issues)', 'wpshadow' ),
					$buffer_size
				);
			}
		}

		// Check for wp_max_upload_size filter that might cause timeouts.
		$wp_max_upload = wp_max_upload_size();
		if ( '0' !== $max_execution_time && (int) $max_execution_time > 0 ) {
			$max_safe_upload = ( (int) $max_execution_time * 0.8 ) * 1048576; // Assume 1MB/s.
			
			if ( $wp_max_upload > $max_safe_upload ) {
				$issues[] = sprintf(
					/* translators: 1: max upload size, 2: recommended size based on timeout */
					__( 'Max upload size (%1$s) exceeds safe limit based on timeout (%2$s recommended)', 'wpshadow' ),
					size_format( $wp_max_upload ),
					size_format( $max_safe_upload )
				);
			}
		}

		// Check for plupload chunk settings.
		$plupload_settings = apply_filters( 'plupload_init', array() );
		if ( ! isset( $plupload_settings['max_retries'] ) || $plupload_settings['max_retries'] < 3 ) {
			$issues[] = __( 'Plupload max_retries not set (failed chunks will not retry)', 'wpshadow' );
		}

		// Check HTTP timeout for remote uploads.
		$http_timeout = apply_filters( 'http_request_timeout', 5 );
		if ( $http_timeout < 30 ) {
			$issues[] = sprintf(
				/* translators: %d: HTTP timeout */
				__( 'HTTP request timeout is %d seconds (remote uploads may fail)', 'wpshadow' ),
				$http_timeout
			);
		}

		// Check for very low timeouts that will definitely cause issues.
		if ( '0' !== $max_execution_time && (int) $max_execution_time < 10 ) {
			$issues[] = __( 'max_execution_time under 10 seconds (critical, most uploads will fail)', 'wpshadow' );
		}

		// Check for timeout-related PHP errors in logs.
		$php_errors = ini_get( 'error_log' );
		if ( $php_errors && file_exists( $php_errors ) && is_readable( $php_errors ) ) {
			$log_content = @file_get_contents( $php_errors, false, null, -8192 ); // Read last ~8KB.
			if ( $log_content ) {
				$timeout_errors = substr_count( $log_content, 'Maximum execution time' );
				if ( $timeout_errors > 5 ) {
					$issues[] = sprintf(
						/* translators: %d: number of timeout errors */
						__( '%d timeout errors found in PHP error log (uploads failing)', 'wpshadow' ),
						$timeout_errors
					);
				}
			}
		}

		// Check if safe_mode is enabled (deprecated but still exists).
		$safe_mode = ini_get( 'safe_mode' );
		if ( $safe_mode && '1' === $safe_mode ) {
			$issues[] = __( 'safe_mode is enabled (deprecated, restricts execution time)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/upload-timeout-errors',
			);
		}

		return null;
	}
}
