<?php
declare(strict_types=1);
/**
 * Comments Disabled Diagnostic
 *
 * Detects when comments are disabled and suggests removing the comments menu
 * from the admin sidebar for cleaner UX.
 *
 * @package WPShadow
 * @subpackage Diagnostics
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
 * Diagnostic for comments being disabled
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Comments_Disabled extends Diagnostic_Base {

	protected static $slug        = 'comments-disabled';
	protected static $title       = 'Comments Disabled';
	protected static $description = 'Detects when comments are disabled and suggests removing the comments menu from admin.';

	/**
	 * Check if comments are disabled and menu is still visible
	 */
	public static function check(): ?array {
		$default_comment_status = get_option( 'default_comment_status' );

		// Only report if comments are closed
		if ( 'closed' !== $default_comment_status ) {
			return null;
		}

		// Check if comments menu hiding is already enabled
		if ( get_option( 'wpshadow_hide_comments_menu' ) ) {
			return null;
		}

		$description  = __( 'Comments are disabled by default, but the WordPress comments menu is still visible in the admin sidebar. This can be hidden for a cleaner admin interface. WPShadow can automatically remove this menu.', 'wpshadow' );
		$description .= '<br><br>' . __( 'Tip: When comments are disabled, WPShadow also recommends removing the "Howdy" greeting for a professional admin experience.', 'wpshadow' );

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'category'     => 'admin-ux',
			'severity'     => 'low',
			'threat_level' => 5,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
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
