<?php declare(strict_types=1);
/**
 * XSS Vulnerability Scanner Diagnostic
 *
 * Philosophy: Vulnerability detection - test for XSS
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Test for reflected XSS vulnerabilities.
 */
class Diagnostic_XSS_Scanner {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Test search with XSS payload
		$test_payload = '<script>alert("XSS")</script>';
		$search_url = add_query_arg( 's', urlencode( $test_payload ), home_url() );
		
		$response = wp_remote_get( $search_url, array( 'timeout' => 10, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}
		
		$body = wp_remote_retrieve_body( $response );
		
		// Check if unescaped script tag appears in response
		if ( stripos( $body, '<script>alert("XSS")</script>' ) !== false ) {
			return array(
				'id'          => 'xss-scanner',
				'title'       => 'Potential XSS Vulnerability',
				'description' => 'Search form or URL parameters may be vulnerable to cross-site scripting (XSS) attacks. User input is being reflected without proper escaping. Use esc_html(), esc_attr(), etc.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/prevent-xss-attacks/',
				'training_link' => 'https://wpshadow.com/training/xss-prevention/',
				'auto_fixable' => false,
				'threat_level' => 80,
			);
		}
		
		return null;
	}
}
