<?php
declare(strict_types=1);
/**
 * Post via Email Category Diagnostic
 *
 * Flags when Post via Email posts are routed to the default "Uncategorized" category.
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check Post via Email default category alignment.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Post_Via_Email_Category extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue or feature disabled.
	 */
	public static function check(): ?array {
		$post_via_email = get_option( 'mailserver_url' );
		if ( empty( $post_via_email ) ) {
			return null;
		}

		$default_category = get_option( 'default_post_category' );
		if ( '1' !== $default_category ) { // 1 is Uncategorized.
			return null;
		}

		return array(
			'id'           => 'post-via-email-default-category',
			'title'        => 'Post via Email Uses "Uncategorized"',
			'description'  => 'Post via Email is enabled but the default post category is "Uncategorized". Assign a dedicated category to prevent unorganized content.',
			'color'        => '#ff9800',
			'bg_color'     => '#fff3e0',
			'category'     => 'settings',
			'auto_fixable' => false,
			'kb_link'      => 'https://wordpress.org/support/article/post-via-email/',
			'threat_level' => 12,
		);
	}

}