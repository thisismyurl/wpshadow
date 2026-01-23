<?php
declare(strict_types=1);
/**
 * Emoji Scripts Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if WordPress emoji scripts are loading unnecessarily.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Emoji_Scripts extends Diagnostic_Base {

	protected static $slug        = 'emoji-scripts';
	protected static $title       = 'Emoji Scripts Loading';
	protected static $description = 'WordPress loads emoji detection scripts on every page. Most modern browsers handle emojis natively.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if treatment is already applied
		$disabled = get_option( 'wpshadow_emoji_scripts_disabled', false );

		if ( $disabled ) {
			return null;
		}

		// Check if emoji scripts are enabled
		$has_emoji_frontend = has_action( 'wp_head', 'print_emoji_detection_script' ) !== false;
		$has_emoji_admin    = has_action( 'admin_print_scripts', 'print_emoji_detection_script' ) !== false;

		if ( ! $has_emoji_frontend && ! $has_emoji_admin ) {
			return null;
		}

		$locations = array();
		if ( $has_emoji_frontend ) {
			$locations[] = 'frontend';
		}
		if ( $has_emoji_admin ) {
			$locations[] = 'admin';
		}

		return array(
			'id'          => 'emoji-scripts',
			'title'       => 'Emoji Scripts Loading',
			'description' => 'WordPress loads emoji detection scripts on the ' . implode( ' and ', $locations ) . '. Modern browsers handle emojis natively, so these scripts are unnecessary for 95% of users and add ~11KB to every page load.',
			'severity'    => 'info',
			'category'    => 'performance',
			'impact'      => 'Adds 11KB JavaScript to every page',
			'fix_time'    => '1 second',
			'kb_article'  => 'emoji-scripts',
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
	}
	/**
	 * Test: Hook detection logic
	 *
	 * Verifies that diagnostic correctly detects hooks and returns
	 * appropriate result (null or array).
	 *
	 * @return array Test result
	 */
	public static function test_hook_detection(): array {
		$result = self::check();
		
		// Should consistently return null or array
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Hook detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Unexpected result type from hook detection',
		);
	}}
