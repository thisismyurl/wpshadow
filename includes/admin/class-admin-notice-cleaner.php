<?php
/**
 * Admin Notice Cleaner for WPShadow
 *
 * Removes unnecessary admin notices on WPShadow pages
 * for a cleaner, faster interface.
 *
 * Philosophy: Inspire Confidence (#8) - Clean UI = trust
 * 
 * @package WPShadow
 * @subpackage Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Notice Cleaner class
 */
class Admin_Notice_Cleaner {
	
	/**
	 * Initialize cleaner
	 */
	public static function init(): void {
		add_action( 'admin_head', [ __CLASS__, 'hide_other_notices' ], 1 );
	}
	
	/**
	 * Hide other plugins' notices on WPShadow pages
	 */
	public static function hide_other_notices(): void {
		if ( ! \function_exists( 'get_current_screen' ) ) {
			return;
		}
		
		$screen = \get_current_screen();
		if ( ! $screen || ! isset( $screen->id ) || strpos( $screen->id, 'wpshadow' ) === false ) {
			return;
		}
		
		// Remove other plugins' admin notices to reduce clutter
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );
		
		// Re-add only WPShadow notices
		add_action( 'admin_notices', [ __CLASS__, 'restore_wpshadow_notices' ] );
	}
	
	/**
	 * Restore only WPShadow-specific notices
	 */
	public static function restore_wpshadow_notices(): void {
		// WPShadow notices will be added by other components
		do_action( 'wpshadow_admin_notices' );
	}
}

// Initialize cleaner
Admin_Notice_Cleaner::init();
