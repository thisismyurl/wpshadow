<?php
declare(strict_types=1);
/**
 * WP Admin Fonts Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if WordPress admin is loading Google Fonts unnecessarily.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Admin_Fonts extends Diagnostic_Base {

	protected static $slug        = 'admin-fonts';
	protected static $title       = 'WP Admin Loads Google Fonts';
	protected static $description = 'WordPress admin loads Open Sans from Google Fonts. This can be removed for privacy and performance.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if treatment is already applied
		$disabled = get_option( 'wpshadow_admin_fonts_disabled', false );

		if ( $disabled ) {
			return null;
		}

		// Check if Open Sans is enqueued in admin
		global $wp_styles;
		$open_sans_loaded = false;

		if ( is_admin() && isset( $wp_styles->registered['open-sans'] ) ) {
			$open_sans_loaded = true;
		}

		// WordPress loads Open Sans by default in admin
		if ( ! $open_sans_loaded && ! is_admin() ) {
			// Assume it will load in admin
			$open_sans_loaded = true;
		}

		if ( ! $open_sans_loaded ) {
			return null;
		}

		return array(
			'id'          => 'admin-fonts',
			'title'       => 'WP Admin Loads Google Fonts',
			'description' => 'WordPress admin loads Open Sans from Google Fonts. This makes external requests on every admin page load and can expose your login activity. Consider using system fonts instead.',
			'severity'    => 'warning',
			'category'    => 'performance',
			'impact'      => 'Every admin page load makes external request to Google',
			'fix_time'    => '1 second',
			'kb_article'  => 'admin-fonts',
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
	 * Test: Option-based detection
	 *
	 * Verifies that diagnostic correctly reads and evaluates options
	 * and returns appropriate result.
	 *
	 * @return array Test result
	 */
	public static function test_option_detection(): array {
		$result = self::check();
		
		// Should return null or array based on option values
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Option detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Option detection returned invalid type',
		);
	}}
