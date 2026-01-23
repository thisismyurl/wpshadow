<?php
declare(strict_types=1);
/**
 * Auth Cookie Domain Scope Diagnostic
 *
 * Philosophy: Cookie security - minimize cookie scope
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check authentication cookie domain configuration.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Auth_Cookie_Domain extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! defined( 'COOKIE_DOMAIN' ) ) {
			return null; // Using default (exact domain)
		}
		
		$cookie_domain = COOKIE_DOMAIN;
		
		// Check if wildcard subdomain is used
		if ( strpos( $cookie_domain, '.' ) === 0 ) {
			// Wildcard like .example.com
			return array(
				'id'          => 'auth-cookie-domain',
				'title'       => 'Overly Broad Cookie Domain',
				'description' => sprintf(
					'COOKIE_DOMAIN is set to "%s" (wildcard subdomain). Authentication cookies will be sent to ALL subdomains, potentially exposing sessions to compromised subdomains. Use exact domain instead.',
					$cookie_domain
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/restrict-cookie-domain/',
				'training_link' => 'https://wpshadow.com/training/cookie-security/',
				'auto_fixable' => false,
				'threat_level' => 70,
			);
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
