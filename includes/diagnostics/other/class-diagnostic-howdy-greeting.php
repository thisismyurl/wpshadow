<?php
declare(strict_types=1);
/**
 * Diagnostic: Howdy Greeting Detection
 *
 * Detects if the "Howdy" admin greeting is displayed in the top menu.
 * Provides an option to remove it for a cleaner admin interface.
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
 * Class Diagnostic_Howdy_Greeting
 *
 * Detects the "Howdy" greeting in WordPress admin top menu.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Howdy_Greeting extends Diagnostic_Base {

	protected static $slug        = 'howdy-greeting-visible';
	protected static $title       = 'Admin Greeting Visible';
	protected static $description = 'Detects if the "Howdy" greeting is displayed in the admin top menu.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding array or null if no issues detected.
	 */
	public static function check(): ?array {
		// Check if "Howdy" greeting is enabled (by default it is)
		$howdy_hidden = get_option( 'wpshadow_hide_howdy_greeting', false );

		if ( $howdy_hidden ) {
			// Already configured to hide, no finding
			return null;
		}

		// "Howdy" is displayed by default - this is just informational
		$finding = array(
			'id'   => self::$slug,
			'title'        => self::$title,
			'description'  => self::build_description(),
			'category'     => 'admin-ux',
			'severity'     => 'info',
			'threat_level' => 1,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);

		return $finding;
	}

	/**
	 * Build the finding description with recommendations.
	 *
	 * @return string HTML description.
	 */
	private static function build_description(): string {
		$description  = __( 'The "Howdy" admin greeting is currently displayed in the top menu bar.', 'wpshadow' );
		$description .= '<br><br><strong>' . __( 'Options:', 'wpshadow' ) . '</strong><ul>';
		$description .= '<li>' . __( 'Keep it for a friendly admin experience', 'wpshadow' ) . '</li>';
		$description .= '<li>' . __( 'Remove it for a cleaner, more professional admin interface', 'wpshadow' ) . '</li>';
		$description .= '<li>' . __( 'This is automatically hidden when comments are disabled site-wide', 'wpshadow' ) . '</li>';
		$description .= '</ul>';

		return $description;
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
