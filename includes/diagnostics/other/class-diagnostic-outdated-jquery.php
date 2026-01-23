<?php
declare(strict_types=1);
/**
 * Outdated jQuery Diagnostic
 *
 * Philosophy: Dependency security - check for vulnerable libraries
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for outdated jQuery with known CVEs.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Outdated_jQuery extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wp_scripts;

		if ( ! isset( $wp_scripts->registered['jquery-core'] ) ) {
			return null;
		}

		$jquery  = $wp_scripts->registered['jquery-core'];
		$version = $jquery->ver;

		// Check if version is older than 3.5.0 (has known XSS vulnerabilities)
		if ( version_compare( $version, '3.5.0', '<' ) ) {
			return array(
				'id'            => 'outdated-jquery',
				'title'         => 'Outdated jQuery Version',
				'description'   => sprintf(
					'Your site uses jQuery %s which has known security vulnerabilities. Update to jQuery 3.5.0 or newer.',
					$version
				),
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/update-jquery-version/',
				'training_link' => 'https://wpshadow.com/training/jquery-security/',
				'auto_fixable'  => false,
				'threat_level'  => 65,
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
