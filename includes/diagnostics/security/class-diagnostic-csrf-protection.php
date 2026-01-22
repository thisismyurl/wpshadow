<?php
declare(strict_types=1);
/**
 * Cross-Site Request Forgery (CSRF) Protection Diagnostic
 *
 * Philosophy: Request security - verify nonce usage
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for CSRF protection in forms.
 */
class Diagnostic_CSRF_Protection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Check for non-admin forms without nonce fields
		$results = $wpdb->get_results(
			"SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%<form%' AND post_content NOT LIKE '%wp_nonce_field%' AND post_type = 'page' LIMIT 5"
		);
		
		if ( ! empty( $results ) ) {
			return array(
				'id'          => 'csrf-protection',
				'title'       => 'Forms Missing CSRF Protection',
				'description' => sprintf(
					'Found %d pages with forms lacking CSRF tokens. This allows attackers to perform unauthorized actions on behalf of users. Add nonce verification to all forms.',
					count( $results )
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/protect-against-csrf/',
				'training_link' => 'https://wpshadow.com/training/csrf-protection/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}
}
