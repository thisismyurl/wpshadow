<?php declare(strict_types=1);
/**
 * Content Security Policy Diagnostic
 *
 * Philosophy: XSS prevention - control resource loading
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if Content Security Policy is configured.
 */
class Diagnostic_CSP {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$response = wp_remote_head( home_url(), array( 'timeout' => 5, 'sslverify' => false ) );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}
		
		$headers = wp_remote_retrieve_headers( $response );
		
		if ( empty( $headers['content-security-policy'] ) && empty( $headers['content-security-policy-report-only'] ) ) {
			return array(
				'id'          => 'csp-header',
				'title'       => 'Content Security Policy Not Configured',
				'description' => 'Your site lacks a Content Security Policy (CSP) header, which helps prevent XSS attacks by controlling which resources can be loaded. Consider implementing CSP.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/implement-content-security-policy/',
				'training_link' => 'https://wpshadow.com/training/csp-security/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}
}
