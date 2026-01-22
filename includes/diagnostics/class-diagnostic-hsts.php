<?php declare(strict_types=1);
/**
 * HTTP Strict Transport Security (HSTS) Diagnostic
 *
 * Philosophy: Security hardening - prevent SSL stripping attacks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if HSTS header is configured.
 */
class Diagnostic_HSTS {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Only check if site uses HTTPS
		if ( ! is_ssl() ) {
			return null;
		}
		
		$response = wp_remote_head( home_url(), array( 'timeout' => 5, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}
		
		$headers = wp_remote_retrieve_headers( $response );
		
		if ( empty( $headers['strict-transport-security'] ) ) {
			return array(
				'id'          => 'hsts-header',
				'title'       => 'HSTS Header Not Configured',
				'description' => 'Your HTTPS site lacks HTTP Strict Transport Security (HSTS) header, making it vulnerable to SSL stripping attacks. Add the Strict-Transport-Security header.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/enable-hsts/',
				'training_link' => 'https://wpshadow.com/training/hsts-security/',
				'auto_fixable' => true,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
