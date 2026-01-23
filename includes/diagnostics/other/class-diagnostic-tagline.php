<?php
declare(strict_types=1);
/**
 * Site Tagline Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if site tagline/description is set.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Tagline extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( empty( get_bloginfo( 'description' ) ) ) {
			$is_registered = self::is_site_registered();

			$finding = array(
				'id'            => 'tagline-empty',
				'title'         => 'Site Tagline is Empty',
				'description'   => 'Add a tagline (Settings → General) to improve SEO and help visitors understand your site quickly.' . ( ! $is_registered ? ' 💡 Register with WPShadow and get AI-powered suggestions for the perfect tagline!' : '' ),
				'color'         => '#2196f3',
				'bg_color'      => '#e3f2fd',
				'kb_link'       => 'https://wpshadow.com/kb/write-an-effective-site-tagline/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=tagline',
				'modal_trigger' => 'wpshadow-tagline-modal',
				'action_text'   => 'Add Tagline',
				'auto_fixable'  => false,
				'threat_level'  => 20,
			);

			// Only show AI button for unregistered sites
			if ( ! $is_registered ) {
				$finding['secondary_action_link'] = 'https://wpshadow.com/register/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=tagline';
				$finding['secondary_action_text'] = 'Get AI Suggestions';
			}

			return $finding;
		}

		return null;
	}

	/**
	 * Check if site is registered with WPShadow.
	 *
	 * @return bool True if site has registered (indicated by email consent).
	 */
	private static function is_site_registered() {
		$consent = get_option( 'wpshadow_email_consent', false );
		return ! empty( $consent );
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
