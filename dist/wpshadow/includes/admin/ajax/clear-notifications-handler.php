<?php
declare( strict_types=1 );

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Gamification\Milestone_Notifier;

/**
 * AJAX Handler: Clear all notifications
 *
 * Action: wp_ajax_wpshadow_clear_notifications
 * Nonce: wpshadow_dashboard_nonce
 * Capability: read
 *
 * @package WPShadow
 */
class Clear_Notifications_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_clear_notifications', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle clear request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_dashboard_nonce', 'read' );

		$user_id = get_current_user_id();
		$success = Milestone_Notifier::clear_all( $user_id );

		if ( ! $success ) {
			self::send_error( __( 'Unable to clear notifications', 'wpshadow' ) );
		}

		self::send_success( array( 'cleared' => true ) );
	}
}
