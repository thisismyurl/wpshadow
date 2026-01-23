<?php
declare(strict_types=1);
/**
 * Dangerous PHP Functions Enabled Diagnostic
 *
 * Philosophy: Server hardening - disable dangerous functions
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if dangerous PHP functions are enabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Dangerous_PHP_Functions extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$dangerous     = array( 'eval', 'exec', 'system', 'passthru', 'shell_exec', 'proc_open', 'popen' );
		$disabled      = ini_get( 'disable_functions' );
		$disabled_list = array_map( 'trim', explode( ',', $disabled ) );

		$enabled_dangerous = array();

		foreach ( $dangerous as $func ) {
			if ( ! in_array( $func, $disabled_list, true ) && function_exists( $func ) ) {
				$enabled_dangerous[] = $func;
			}
		}

		if ( ! empty( $enabled_dangerous ) ) {
			return array(
				'id'            => 'dangerous-php-functions',
				'title'         => 'Dangerous PHP Functions Enabled',
				'description'   => sprintf(
					'Dangerous functions enabled: %s. These allow remote code execution. Disable via php.ini: disable_functions = %s',
					implode( ', ', $enabled_dangerous ),
					implode( ', ', $enabled_dangerous )
				),
				'severity'      => 'critical',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/disable-dangerous-php-functions/',
				'training_link' => 'https://wpshadow.com/training/php-hardening/',
				'auto_fixable'  => false,
				'threat_level'  => 95,
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
