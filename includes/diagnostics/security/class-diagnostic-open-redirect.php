<?php
declare(strict_types=1);
/**
 * Open Redirect Vulnerability Diagnostic
 *
 * Philosophy: Phishing prevention - validate redirects
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test for open redirect vulnerabilities.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Open_Redirect extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Test common redirect parameters
		$test_params = array(
			'redirect_to' => 'https://evil.example.com',
			'redirect' => 'https://evil.example.com',
			'return' => 'https://evil.example.com',
			'url' => 'https://evil.example.com',
		);
		
		foreach ( $test_params as $param => $value ) {
			$test_url = add_query_arg( $param, urlencode( $value ), wp_login_url() );
			$response = wp_remote_head( $test_url, array( 
				'timeout' => 5, 
				'sslverify' => false,
				'redirection' => 0
			) );
			
			if ( is_wp_error( $response ) ) {
				continue;
			}
			
			$status = wp_remote_retrieve_response_code( $response );
			$location = wp_remote_retrieve_header( $response, 'location' );
			
			// Check if it redirects to external domain
			if ( in_array( $status, array( 301, 302, 303, 307, 308 ), true ) &&
			     ! empty( $location ) &&
			     strpos( $location, 'evil.example.com' ) !== false ) {
				
				return array(
					'id'          => 'open-redirect',
					'title'       => 'Open Redirect Vulnerability',
					'description' => sprintf(
						'Your login/redirect flow has an open redirect vulnerability via "%s" parameter. Attackers can use this for phishing by sending users to malicious sites. Validate all redirect URLs.',
						$param
					),
					'severity'    => 'high',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/prevent-open-redirects/',
					'training_link' => 'https://wpshadow.com/training/redirect-security/',
					'auto_fixable' => false,
					'threat_level' => 70,
				);
			}
		}
		
		return null;
	}
}
