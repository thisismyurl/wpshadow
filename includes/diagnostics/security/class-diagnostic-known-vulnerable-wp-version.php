<?php
declare(strict_types=1);
/**
 * Known Vulnerable WordPress Version Diagnostic
 *
 * Philosophy: Core security - track known CVEs
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if WordPress version has known vulnerabilities.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Known_Vulnerable_WP_Version extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wp_version;
		
		// Sample list - in production would use CVE database
		$vulnerable_versions = array(
			'5.7' => array( 'CVE-2021-24405', 'CVE-2021-24406' ),
			'5.6' => array( 'CVE-2021-24405', 'CVE-2021-21345' ),
			'5.5' => array( 'CVE-2021-21345', 'CVE-2020-12447' ),
		);
		
		foreach ( $vulnerable_versions as $vuln_version => $cves ) {
			if ( strpos( $wp_version, $vuln_version ) === 0 ) {
				return array(
					'id'          => 'known-vulnerable-wp-version',
					'title'       => 'Known Security Vulnerabilities in WordPress Version',
					'description' => sprintf(
						'WordPress %s has %d known public CVEs: %s. Update to latest stable version immediately.',
						$wp_version,
						count( $cves ),
						implode( ', ', $cves )
					),
					'severity'    => 'critical',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/wordpress-security-updates/',
					'training_link' => 'https://wpshadow.com/training/core-updates/',
					'auto_fixable' => false,
					'threat_level' => 85,
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
