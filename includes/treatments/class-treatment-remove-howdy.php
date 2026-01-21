<?php
/**
 * Treatment: Remove Howdy Greeting
 *
 * Removes the "Howdy" greeting from the WordPress admin top menu bar.
 *
 * @package WPShadow
 * @subpackage Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Treatment_Remove_Howdy
 *
 * Implements the treatment to remove the "Howdy" greeting from admin bar.
 */
class Treatment_Remove_Howdy extends Treatment_Base {

	/**
	 * Get the finding ID this treatment applies to.
	 *
	 * @return string Finding ID.
	 */
	public static function get_finding_id(): string {
		return 'howdy-greeting-visible';
	}
	/**
	 * Apply the treatment.
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function apply(): array {
		update_option( 'wpshadow_hide_howdy_greeting', 1 );
		add_action( 'admin_bar_menu', array( __CLASS__, 'remove_howdy_greeting' ), 25 );
		
		KPI_Tracker::log_fix_applied( self::get_finding_id(), 'auto' );
		
		return array(
			'success' => true,
			'message' => __( 'Howdy greeting has been removed from the admin bar.', 'wpshadow' ),
		);
	}

	/**
	 * Undo/revert the treatment.
	 *
	 * @return array Result with 'success' bool and 'message' string.
	 */
	public static function undo(): array {
		delete_option( 'wpshadow_hide_howdy_greeting' );
		
		KPI_Tracker::log_fix_undone( self::get_finding_id() );
		
		return array(
			'success' => true,
			'message' => __( 'Howdy greeting has been restored to the admin bar.', 'wpshadow' ),
		);
	}

	/**
	 * Remove the Howdy greeting from admin bar.
	 *
	 * Hooks to admin_bar_menu and removes the user greeting node.
	 *
	 * @param object $wp_admin_bar WP_Admin_Bar instance.
	 * @return void
	 */
	public static function remove_howdy_greeting( $wp_admin_bar ): void {
		$wp_admin_bar->remove_node( 'my-account' );
	}
}
