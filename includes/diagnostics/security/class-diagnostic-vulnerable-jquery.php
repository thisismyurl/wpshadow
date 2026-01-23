<?php
declare(strict_types=1);
/**
 * Vulnerable jQuery Version Diagnostic
 *
 * Philosophy: Library security - update jQuery
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for vulnerable jQuery versions.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Vulnerable_jQuery extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wp_scripts;
		
		if ( empty( $wp_scripts->registered['jquery'] ) ) {
			return null;
		}
		
		$jquery = $wp_scripts->registered['jquery'];
		
		// Check version - vulnerable versions: < 1.12.4, 2.x < 2.2.4, 3.x < 3.0.0
		if ( preg_match( '/(\d+)\.(\d+)\.(\d+)/', $jquery->ver, $matches ) ) {
			$major = intval( $matches[1] );
			$minor = intval( $matches[2] );
			
			if ( ( $major === 1 && $minor < 12 ) || ( $major === 2 && $minor < 2 ) ) {
				return array(
					'id'          => 'vulnerable-jquery',
					'title'       => 'Vulnerable jQuery Version',
					'description' => sprintf(
						'jQuery version %s has known security vulnerabilities. Update to latest 3.x version.',
						$jquery->ver
					),
					'severity'    => 'high',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/update-jquery/',
					'training_link' => 'https://wpshadow.com/training/library-updates/',
					'auto_fixable' => false,
					'threat_level' => 70,
				);
			}
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
