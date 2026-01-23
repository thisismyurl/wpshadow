<?php
declare(strict_types=1);
/**
 * XML-RPC Security Diagnostic
 *
 * Philosophy: Security hardening - disable unused attack vector
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if XML-RPC is disabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_XMLRPC_Disabled extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if xmlrpc.php is accessible
		$xmlrpc_url = site_url( 'xmlrpc.php' );
		$response   = wp_remote_post(
			$xmlrpc_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
				'body'      => '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName></methodCall>',
				'headers'   => array( 'Content-Type' => 'text/xml' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return null; // Can't check or blocked (good)
		}

		$status = wp_remote_retrieve_response_code( $response );

		// If XML-RPC responds, it's enabled
		if ( $status === 200 ) {
			$body = wp_remote_retrieve_body( $response );
			if ( strpos( $body, 'methodResponse' ) !== false ) {
				return array(
					'id'            => 'xmlrpc-disabled',
					'title'         => 'XML-RPC Enabled',
					'description'   => 'XML-RPC is enabled and can be exploited for DDoS amplification and brute force attacks. Disable it unless you need pingbacks or remote publishing.',
					'severity'      => 'medium',
					'category'      => 'security',
					'kb_link'       => 'https://wpshadow.com/kb/disable-xmlrpc/',
					'training_link' => 'https://wpshadow.com/training/xmlrpc-security/',
					'auto_fixable'  => true,
					'threat_level'  => 70,
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
