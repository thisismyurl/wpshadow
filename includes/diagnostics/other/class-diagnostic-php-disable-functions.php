<?php
declare(strict_types=1);
/**
 * PHP Disable Functions Diagnostic
 *
 * Philosophy: Code execution security - disable dangerous functions
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if dangerous PHP functions are disabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_PHP_Disable_Functions extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$disabled_functions = ini_get( 'disable_functions' );
		$disabled_array     = array_map( 'trim', explode( ',', $disabled_functions ) );

		// Dangerous functions that should be disabled
		$dangerous = array(
			'exec',
			'passthru',
			'shell_exec',
			'system',
			'proc_open',
			'popen',
			'curl_exec',
			'curl_multi_exec',
			'parse_ini_file',
			'show_source',
			'eval',
			'assert',
		);

		$enabled_dangerous = array();

		foreach ( $dangerous as $func ) {
			if ( ! in_array( $func, $disabled_array, true ) && function_exists( $func ) ) {
				$enabled_dangerous[] = $func;
			}
		}

		if ( count( $enabled_dangerous ) > 5 ) {
			return array(
				'id'            => 'php-disable-functions',
				'title'         => 'Dangerous PHP Functions Not Disabled',
				'description'   => sprintf(
					'Your PHP configuration allows dangerous functions: %s. These enable remote code execution if exploited. Disable via php.ini: disable_functions = "%s"',
					implode( ', ', array_slice( $enabled_dangerous, 0, 5 ) ),
					implode( ', ', $enabled_dangerous )
				),
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/disable-dangerous-php-functions/',
				'training_link' => 'https://wpshadow.com/training/php-hardening/',
				'auto_fixable'  => false,
				'threat_level'  => 75,
			);
		}

		return null;
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
