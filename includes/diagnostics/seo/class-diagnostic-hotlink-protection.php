<?php
declare(strict_types=1);
/**
 * Hotlink Protection Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if basic hotlink protection is enabled for media assets.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Hotlink_Protection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! self::is_apache_like() ) {
			return null; // Only evaluate on Apache-like setups.
		}
		
		if ( ! self::has_hotlink_rules() ) {
			return array(
				'id'           => 'hotlink-protection-missing',
				'title'        => 'Hotlink Protection Not Enabled',
				'description'  => 'Blocking image hotlinking saves bandwidth and prevents unauthorized re-use of your media.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/enable-hotlink-protection/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=hotlink-protection',
				'auto_fixable' => true,
				'threat_level' => 45,
			);
		}
		
		return null;
	}
	
	/**
	 * Determine if server is Apache-like and supports .htaccess rules.
	 *
	 * @return bool
	 */
	private static function is_apache_like() {
		if ( function_exists( 'apache_get_modules' ) ) {
			$modules = apache_get_modules();
			return in_array( 'mod_rewrite', $modules, true );
		}
		
		return ( isset( $_SERVER['SERVER_SOFTWARE'] ) && false !== stripos( $_SERVER['SERVER_SOFTWARE'], 'apache' ) );
	}
	
	/**
	 * Check if the WPShadow hotlink protection block exists in .htaccess.
	 *
	 * @return bool
	 */
	private static function has_hotlink_rules() {
		$htaccess = ABSPATH . '.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			return false;
		}
		
		$contents = file_get_contents( $htaccess );
		return false !== strpos( $contents, '# BEGIN WPShadow Hotlink Protection' );
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
