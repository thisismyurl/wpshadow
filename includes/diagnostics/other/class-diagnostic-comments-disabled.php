<?php
declare(strict_types=1);
/**
 * Comments Disabled Diagnostic
 *
 * Detects when comments are disabled and suggests removing the comments menu
 * from the admin sidebar for cleaner UX.
 *
 * @package WPShadow
 * @subpackage Diagnostics
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic for comments being disabled
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Comments_Disabled extends Diagnostic_Base {

	protected static $slug        = 'comments-disabled';
	protected static $title       = 'Comments Disabled';
	protected static $description = 'Detects when comments are disabled and suggests removing the comments menu from admin.';

	/**
	 * Check if comments are disabled and menu is still visible
	 */
	public static function check(): ?array {
		$default_comment_status = get_option( 'default_comment_status' );

		// Only report if comments are closed
		if ( 'closed' !== $default_comment_status ) {
			return null;
		}

		// Check if comments menu hiding is already enabled
		if ( get_option( 'wpshadow_hide_comments_menu' ) ) {
			return null;
		}

		$description  = __( 'Comments are disabled by default, but the WordPress comments menu is still visible in the admin sidebar. This can be hidden for a cleaner admin interface. WPShadow can automatically remove this menu.', 'wpshadow' );
		$description .= '<br><br>' . __( 'Tip: When comments are disabled, WPShadow also recommends removing the "Howdy" greeting for a professional admin experience.', 'wpshadow' );

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'category'     => 'admin-ux',
			'severity'     => 'low',
			'threat_level' => 5,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

}