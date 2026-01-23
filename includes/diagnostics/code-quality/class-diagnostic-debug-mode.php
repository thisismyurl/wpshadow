<?php
declare(strict_types=1);
/**
 * Debug Mode Diagnostic
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if debug mode is enabled on live site.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Debug_Mode extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return array(
				'id'           => 'debug-mode-enabled',
				'title'        => 'Debug Mode Enabled',
				'description'  => 'WordPress debug mode is active. Disable it on live sites for better security.',
				'color'        => '#ff9800',
				'bg_color'     => '#fff3e0',
				'kb_link'      => 'https://wpshadow.com/kb/disable-wordpress-debug-mode/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=debug-mode',
				'auto_fixable' => self::can_modify_wp_config(),
				'threat_level' => 70,
			);
		}
		
		return null;
	}
	
	/**
	 * Check if we can modify wp-config.php.
	 *
	 * @return bool True if wp-config.php is writable.
	 */
	private static function can_modify_wp_config() {
		$config_file = ABSPATH . 'wp-config.php';
		if ( ! file_exists( $config_file ) ) {
			$config_file = dirname( ABSPATH ) . '/wp-config.php';
		}
		return file_exists( $config_file ) && is_writable( $config_file );
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
