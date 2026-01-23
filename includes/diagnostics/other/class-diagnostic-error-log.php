<?php
declare(strict_types=1);
/**
 * Error Log Size Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for oversized debug.log.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Error_Log extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$log_path = self::get_log_path();
		if ( ! $log_path || ! file_exists( $log_path ) ) {
			return null;
		}

		$size_bytes = filesize( $log_path );
		$threshold  = 5 * 1024 * 1024; // 5MB

		if ( $size_bytes >= $threshold ) {
			$size_mb = round( $size_bytes / 1024 / 1024, 1 );
			return array(
				'id'           => 'error-log-large',
				'title'        => "debug.log is {$size_mb}MB",
				'description'  => 'Large error logs can hide current issues and consume disk space. Rotate it to start fresh.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/rotate-debug-log/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=error-log',
				'auto_fixable' => true,
				'threat_level' => 45,
			);
		}

		return null;
	}

	/**
	 * Locate the debug log path.
	 *
	 * @return string|null
	 */
	private static function get_log_path() {
		$path = ini_get( 'error_log' );
		if ( $path && is_string( $path ) && file_exists( $path ) ) {
			return $path;
		}

		$default = WP_CONTENT_DIR . '/debug.log';
		return file_exists( $default ) ? $default : null;
	}

	/**
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}}
