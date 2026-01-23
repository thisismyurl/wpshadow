<?php
declare(strict_types=1);
/**
 * XSS Vulnerability Pattern Detection Diagnostic
 *
 * Philosophy: Output security - prevent cross-site scripting
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for XSS vulnerabilities.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_XSS_Patterns extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$plugins_dir = WP_PLUGIN_DIR;
		$files = glob( $plugins_dir . '/*/*.php' );
		
		foreach ( $files as $file ) {
			$content = file_get_contents( $file );
			
			// Look for unescaped output of user input
			if ( preg_match( '/echo\s+\$_(?:GET|POST|REQUEST)(?![^"\']*(?:esc_html|esc_attr|wp_kses))/', $content ) ) {
				return array(
					'id'          => 'xss-patterns',
					'title'       => 'Cross-Site Scripting (XSS) Vulnerability Pattern',
					'description' => 'Code outputs user input without escaping. Attackers inject malicious JavaScript. Always escape output: echo esc_html( $var ).',
					'severity'    => 'critical',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/prevent-xss-attacks/',
					'training_link' => 'https://wpshadow.com/training/output-escaping/',
					'auto_fixable' => false,
					'threat_level' => 90,
				);
			}
		}
		
		return null;
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
