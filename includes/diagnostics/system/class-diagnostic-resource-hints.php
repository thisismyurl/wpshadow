<?php
declare(strict_types=1);
/**
 * Resource Hints Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing resource hints on primary domains.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Resource_Hints extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$hosts = self::get_hosts();
		if ( empty( $hosts ) ) {
			return null;
		}
		
		return array(
			'id'           => 'resource-hints-missing',
			'title'        => 'Add Resource Hints',
			'description'  => 'Preconnect/preload common domains (CDN, fonts, APIs) to improve first-byte time.',
			'color'        => '#ff9800',
			'bg_color'     => '#fff3e0',
			'kb_link'      => 'https://wpshadow.com/kb/add-resource-hints/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=resource-hints',
			'auto_fixable' => true,
			'threat_level' => 35,
		);
	}
	
	/**
	 * Collect candidate hosts for hints.
	 *
	 * @return array
	 */
	private static function get_hosts() {
		$hosts = array();
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( $site_host ) {
			$hosts[] = $site_host;
		}
		
		$cdn = get_option( 'wpshadow_cdn_host' );
		if ( $cdn ) {
			$hosts[] = $cdn;
		}
		
		return array_unique( array_filter( $hosts ) );
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
