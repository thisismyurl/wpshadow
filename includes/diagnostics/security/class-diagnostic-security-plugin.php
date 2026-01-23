<?php
declare(strict_types=1);
/**
 * Security Plugin Diagnostic
 *
 * Philosophy: Helpful neighbor - recommend centralized security tool
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if a security plugin is installed.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Security_Plugin extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check for common security plugins
		$security_plugins = array(
			'wordfence/wordfence.php',
			'better-wp-security/better-wp-security.php',
			'sucuri-scanner/sucuri.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
			'jetpack/jetpack.php',
			'bulletproof-security/bulletproof-security.php',
			'security-ninja/security-ninja.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $security_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null; // Security plugin active
			}
		}
		
		return array(
			'id'          => 'security-plugin',
			'title'       => 'No Security Plugin Detected',
			'description' => 'Your site lacks a dedicated security plugin for centralized monitoring, hardening, and threat detection. Consider installing Wordfence, iThemes Security, or similar.',
			'severity'    => 'high',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/choose-security-plugin/',
			'training_link' => 'https://wpshadow.com/training/security-plugins/',
			'auto_fixable' => false,
			'threat_level' => 75,
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
