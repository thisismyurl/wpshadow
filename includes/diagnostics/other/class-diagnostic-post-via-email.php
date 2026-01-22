<?php
declare(strict_types=1);
/**
 * Post via Email Enabled Diagnostic
 *
 * Flags security risk when legacy Post via Email is enabled.
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for Post via Email being enabled.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Post_Via_Email extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$post_via_email = get_option( 'mailserver_url' );
		if ( empty( $post_via_email ) ) {
			return null;
		}

		return array(
			'id'           => 'post-via-email-enabled',
			'title'        => 'Post via Email Enabled',
			'description'  => 'Post via Email is enabled. This legacy workflow expands your attack surface if the mailbox is compromised. Disable it unless it is actively used.',
			'color'        => '#ff9800',
			'bg_color'     => '#fff3e0',
			'category'     => 'settings',
			'auto_fixable' => false,
			'kb_link'      => 'https://wordpress.org/support/article/post-via-email/',
			'threat_level' => 16,
		);
	}
}
