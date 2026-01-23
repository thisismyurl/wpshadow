<?php
declare(strict_types=1);
/**
 * Missing Authorization Checks Diagnostic
 *
 * Philosophy: Access control - require capability checks
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for missing authorization on sensitive actions.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Missing_Authorization_Checks extends Diagnostic_Base {
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
			
			// Look for add_action without capability check nearby
			if ( preg_match( '/add_action\s*\(\s*[\'"]admin_init[\'"].*?function\s*\(\s*\)\s*\{[^}]{0,500}update_option|update_post_meta/s', $content ) ) {
				// Check if current_user_can is missing
				if ( ! preg_match( '/current_user_can/', substr( $content, strpos( $content, 'add_action' ), 500 ) ) ) {
					return array(
						'id'          => 'missing-authorization-checks',
						'title'       => 'Missing Authorization Checks on Sensitive Actions',
						'description' => 'Code updates options/posts without checking user capabilities. Add current_user_can() checks before any sensitive operations.',
						'severity'    => 'high',
						'category'    => 'security',
						'kb_link'     => 'https://wpshadow.com/kb/add-authorization-checks/',
						'training_link' => 'https://wpshadow.com/training/capability-checking/',
						'auto_fixable' => false,
						'threat_level' => 80,
					);
				}
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
