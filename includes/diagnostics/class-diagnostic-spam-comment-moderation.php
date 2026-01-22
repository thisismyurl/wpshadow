<?php declare(strict_types=1);
/**
 * Spam/Comment Moderation System Diagnostic
 *
 * Philosophy: Content security - filter spam and malicious comments
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if spam filtering is configured.
 */
class Diagnostic_Spam_Comment_Moderation {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$spam_plugins = array(
			'akismet/akismet.php',
			'wp-spamshield/wp-spamshield.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $spam_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}
		
		// Check if Akismet is configured via core
		$akismet_key = get_option( 'akismet_api_key' );
		if ( ! empty( $akismet_key ) ) {
			return null;
		}
		
		return array(
			'id'          => 'spam-comment-moderation',
			'title'       => 'No Spam/Comment Filtering',
			'description' => 'Comments not filtered for spam or malicious content. Spam comments boost SEO of attacker sites and can contain malware links. Enable Akismet or similar spam filter.',
			'severity'    => 'low',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/enable-spam-filtering/',
			'training_link' => 'https://wpshadow.com/training/comment-moderation/',
			'auto_fixable' => false,
			'threat_level' => 50,
		);
	}
}
