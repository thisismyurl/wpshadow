<?php
/**
 * Remove Comments Menu Treatment
 *
 * Hides WordPress comments menu from admin when comments are disabled.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment for removing comments menu when disabled
 */
class Treatment_Remove_Comments_Menu implements Treatment_Interface {
	/**
	 * Get the finding ID this treatment addresses
	 *
	 * @return string
	 */
	public static function get_finding_id() {
		return 'comments-disabled';
	}

	/**
	 * Check if this treatment can be applied
	 *
	 * @return bool True if comments are disabled globally
	 */
	public static function can_apply() {
		// Check if comments are disabled
		$default_comment_status = get_option( 'default_comment_status' );
		return 'closed' === $default_comment_status;
	}

	/**
	 * Apply the treatment - hide comments menu
	 *
	 * @return array Result with 'success' bool and 'message' string
	 */
	public static function apply() {
		if ( ! self::can_apply() ) {
			return array(
				'success' => false,
				'message' => 'Comments are not disabled. Comments menu will remain visible.',
			);
		}

		// Store the option to hide comments menu
		update_option( 'wpshadow_hide_comments_menu', true );
		
		// Hook to remove comments menu on next admin load
		add_action( 'admin_menu', array( __CLASS__, 'hide_comments_menu' ) );
		
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		
		return array(
			'success' => true,
			'message' => 'Comments menu has been hidden from the admin sidebar.',
		);
	}

	/**
	 * Undo the treatment - restore comments menu
	 *
	 * @return array Result with 'success' bool and 'message' string
	 */
	public static function undo() {
		delete_option( 'wpshadow_hide_comments_menu' );
		
		return array(
			'success' => true,
			'message' => 'Comments menu will be visible again on next admin load.',
		);
	}

	/**
	 * Remove comments menu from admin sidebar
	 *
	 * Should be hooked to 'admin_menu' action
	 */
	public static function hide_comments_menu() {
		if ( get_option( 'wpshadow_hide_comments_menu' ) ) {
			remove_menu_page( 'edit-comments.php' );
		}
	}
}
