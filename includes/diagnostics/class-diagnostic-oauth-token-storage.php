<?php declare(strict_types=1);
/**
 * OAuth Token Storage Diagnostic
 *
 * Philosophy: Token security - secure OAuth token storage
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check OAuth token storage security.
 */
class Diagnostic_OAuth_Token_Storage {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Check for OAuth plugins
		$oauth_plugins = array(
			'oauth2-provider/oauth2-provider.php',
			'wp-oauth-server/wp-oauth-server.php',
			'miniorange-oauth-20-server/miniorange_oauth_server.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		$has_oauth = false;
		
		foreach ( $oauth_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				$has_oauth = true;
				break;
			}
		}
		
		if ( ! $has_oauth ) {
			return null; // No OAuth
		}
		
		// Check if tokens are stored in database (common pattern)
		global $wpdb;
		$token_tables = $wpdb->get_results(
			"SHOW TABLES LIKE '{$wpdb->prefix}%oauth%token%'"
		);
		
		if ( ! empty( $token_tables ) ) {
			return array(
				'id'          => 'oauth-token-storage',
				'title'       => 'OAuth Tokens in Database',
				'description' => 'OAuth tokens are stored in database tables. Tokens should be stored in httpOnly, Secure cookies or encrypted at rest. Database storage exposes tokens via SQL injection or backups.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-oauth-tokens/',
				'training_link' => 'https://wpshadow.com/training/oauth-security/',
				'auto_fixable' => false,
				'threat_level' => 85,
			);
		}
		
		return null;
	}
}
