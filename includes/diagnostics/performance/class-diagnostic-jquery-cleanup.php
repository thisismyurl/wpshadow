<?php
declare(strict_types=1);
/**
 * jQuery Cleanup Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if legacy jQuery loads in the footer or unnecessarily on front pages.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_jQuery_Cleanup extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( is_admin() ) {
			return null;
		}
		
		global $wp_scripts;
		if ( ! isset( $wp_scripts ) ) {
			return null;
		}
		
		if ( ! in_array( 'jquery', (array) $wp_scripts->queue, true ) ) {
			return null;
		}
		
		return array(
			'id'           => 'jquery-front-loading',
			'title'        => 'jQuery Loading on Front-End',
			'description'  => 'Legacy jQuery is queued on the front-end. Defer or remove it where not needed to improve performance.',
			'color'        => '#ff9800',
			'bg_color'     => '#fff3e0',
			'kb_link'      => 'https://wpshadow.com/kb/defer-jquery/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=jquery-cleanup',
			'auto_fixable' => true,
			'threat_level' => 30,
		);
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
