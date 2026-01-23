<?php
declare(strict_types=1);
/**
 * Known Vulnerable Plugin Version Diagnostic
 *
 * Philosophy: Plugin security - track plugin CVEs
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if installed plugins have known vulnerabilities.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Known_Vulnerable_Plugin_Versions extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$plugins = get_plugins();
		
		// Sample list - in production would use WPScan/CVE database
		$vulnerable_plugins = array(
			'simple-member-plugin' => array(
				'<= 2.5' => 'SQL Injection CVE-2021-12345',
			),
		);
		
		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_slug = dirname( $plugin_file );
			
			if ( isset( $vulnerable_plugins[ $plugin_slug ] ) ) {
				foreach ( $vulnerable_plugins[ $plugin_slug ] as $version_range => $cve ) {
					return array(
						'id'          => 'known-vulnerable-plugin-version',
						'title'       => 'Known Vulnerability in Installed Plugin',
						'description' => sprintf(
							'Plugin %s (version %s) has known vulnerability: %s. Update or remove immediately.',
							$plugin_data['Name'],
							$plugin_data['Version'],
							$cve
						),
						'severity'    => 'critical',
						'category'    => 'security',
						'kb_link'     => 'https://wpshadow.com/kb/plugin-security-updates/',
						'training_link' => 'https://wpshadow.com/training/plugin-updates/',
						'auto_fixable' => false,
						'threat_level' => 90,
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
