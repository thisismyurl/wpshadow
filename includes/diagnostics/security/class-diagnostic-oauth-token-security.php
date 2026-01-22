<?php
declare(strict_types=1);
/**
 * OAuth Token Security Diagnostic
 *
 * Philosophy: Third-party auth - secure token handling
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for secure OAuth token storage.
 */
class Diagnostic_OAuth_Token_Security extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Check for OAuth tokens in user meta or options (should be encrypted)
		$results = $wpdb->get_results(
			"SELECT COUNT(*) as count FROM {$wpdb->usermeta} WHERE meta_key LIKE '%oauth%' OR meta_key LIKE '%token%'"
		);
		
		if ( ! empty( $results[0]->count ) && $results[0]->count > 0 ) {
			// Tokens found - check if encrypted
			$tokens = $wpdb->get_results(
				"SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key LIKE '%oauth%' LIMIT 1"
			);
			
			if ( ! empty( $tokens ) ) {
				$token_value = $tokens[0]->meta_value;
				
				// Check if it looks encrypted
				if ( ! preg_match( '/^[a-f0-9]+$/', $token_value ) && strlen( $token_value ) > 100 ) {
					return array(
						'id'          => 'oauth-token-security',
						'title'       => 'OAuth Tokens May Not Be Encrypted',
						'description' => 'OAuth tokens stored in database without encryption. Compromised database exposes third-party accounts. Encrypt sensitive tokens at rest.',
						'severity'    => 'high',
						'category'    => 'security',
						'kb_link'     => 'https://wpshadow.com/kb/encrypt-oauth-tokens/',
						'training_link' => 'https://wpshadow.com/training/token-security/',
						'auto_fixable' => false,
						'threat_level' => 75,
					);
				}
			}
		}
		
		return null;
	}
}
