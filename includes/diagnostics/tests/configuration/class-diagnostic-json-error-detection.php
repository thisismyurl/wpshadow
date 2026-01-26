<?php
/**
 * Diagnostic: JSON Error Detection
 *
 * Scans for recent JSON errors in WordPress debug logs or error logs.
 * JSON errors can break REST API, Gutenberg, and AJAX functionality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Json_Error_Detection
 *
 * Detects JSON-related errors in WordPress.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Json_Error_Detection extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'json-error-detection';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'JSON Error Detection';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Scans for JSON errors in logs and recent operations';

	/**
	 * Check for JSON errors.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$errors = array();

		// Check if JSON error tracking is available.
		if ( ! function_exists( 'json_last_error' ) ) {
			return null;
		}

		// Check for JSON errors in recent transients.
		$json_error_transient = get_transient( 'wpshadow_json_errors' );
		if ( is_array( $json_error_transient ) && ! empty( $json_error_transient ) ) {
			$errors = array_merge( $errors, $json_error_transient );
		}

		// Check debug.log if WP_DEBUG_LOG is enabled.
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			$log_file = WP_CONTENT_DIR . '/debug.log';

			if ( file_exists( $log_file ) && is_readable( $log_file ) ) {
				// Read last 1000 lines.
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen, WordPress.WP.AlternativeFunctions.file_system_operations_fclose
				$handle = fopen( $log_file, 'r' );
				if ( $handle ) {
					// Get file size.
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fseek
					fseek( $handle, -min( filesize( $log_file ), 50000 ), SEEK_END );
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
					$contents = fread( $handle, 50000 );
					// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
					fclose( $handle );

					// Search for JSON errors.
					if ( preg_match_all( '/json[_\s]*(encode|decode|error)/i', $contents, $matches ) ) {
						$json_error_count = count( $matches[0] );
						if ( $json_error_count > 0 ) {
							$errors[] = sprintf(
								/* translators: %d: Number of JSON errors */
								__( '%d JSON-related error(s) found in debug.log', 'wpshadow' ),
								$json_error_count
							);
						}
					}
				}
			}
		}

		// Check for JSON errors in WordPress error log.
		$error_log_file = ini_get( 'error_log' );
		if ( $error_log_file && file_exists( $error_log_file ) && is_readable( $error_log_file ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen, WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			$handle = fopen( $error_log_file, 'r' );
			if ( $handle ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fseek
				fseek( $handle, -min( filesize( $error_log_file ), 50000 ), SEEK_END );
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
				$contents = fread( $handle, 50000 );
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
				fclose( $handle );

				if ( preg_match_all( '/json[_\s]*(encode|decode|error)/i', $contents, $matches ) ) {
					$json_error_count = count( $matches[0] );
					if ( $json_error_count > 0 ) {
						$errors[] = sprintf(
							/* translators: %d: Number of JSON errors */
							__( '%d JSON-related error(s) found in PHP error log', 'wpshadow' ),
							$json_error_count
						);
					}
				}
			}
		}

		if ( ! empty( $errors ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: List of errors */
					__( 'JSON errors detected: %s', 'wpshadow' ),
					implode( '; ', $errors )
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/json_error_detection',
				'meta'        => array(
					'errors' => $errors,
				),
			);
		}

		// No JSON errors detected.
		return null;
	}
}
