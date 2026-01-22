<?php declare(strict_types=1);
/**
 * User Enumeration Protection Diagnostic
 *
 * Philosophy: Security hardening - prevents username discovery
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if user enumeration is blocked.
 */
class Diagnostic_User_Enumeration {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Test if /?author=1 reveals usernames
		$test_url = add_query_arg( 'author', 1, home_url() );
		$response = wp_remote_head( $test_url, array( 'timeout' => 5, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null; // Can't check
		}
		
		$status = wp_remote_retrieve_response_code( $response );
		
		// If redirect to author archive succeeds, enumeration is possible
		if ( $status === 200 || $status === 301 || $status === 302 ) {
			return array(
				'id'          => 'user-enumeration',
				'title'       => 'User Enumeration Enabled',
				'description' => 'Attackers can discover usernames via /?author=N URLs. Block author archives or use a security plugin to prevent username discovery.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/prevent-user-enumeration/',
				'training_link' => 'https://wpshadow.com/training/user-enumeration/',
				'auto_fixable' => true,
				'threat_level' => 65,
			);
		}
		
		return null;
	}
}
