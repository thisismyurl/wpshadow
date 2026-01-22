<?php declare(strict_types=1);
/**
 * Directory Listing Security Diagnostic
 *
 * Philosophy: Security hardening - prevent file enumeration
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if directory listing is disabled.
 */
class Diagnostic_Directory_Listing {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Test uploads directory
		$upload_dir = wp_upload_dir();
		$test_url = trailingslashit( $upload_dir['baseurl'] );
		
		$response = wp_remote_head( $test_url, array( 'timeout' => 5, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null; // Can't check
		}
		
		$body = wp_remote_retrieve_body( wp_remote_get( $test_url, array( 'timeout' => 5, 'sslverify' => false ) ) );
		
		// Check for directory listing indicators
		if ( strpos( $body, 'Index of' ) !== false || strpos( $body, 'Parent Directory' ) !== false ) {
			return array(
				'id'          => 'directory-listing',
				'title'       => 'Directory Listing Enabled',
				'description' => 'Your uploads directory allows file listing, exposing your file structure. Disable directory indexes via .htaccess or server config.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/disable-directory-listing/',
				'training_link' => 'https://wpshadow.com/training/directory-listing/',
				'auto_fixable' => true,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
