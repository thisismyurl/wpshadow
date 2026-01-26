<?php
declare( strict_types=1 );

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Gamification\Milestone_Notifier;

/**
 * AJAX Handler: Mark notification as read
 *
 * Action: wp_ajax_wpshadow_mark_notification_read
 * Nonce: wpshadow_dashboard_nonce
 * Capability: read
 *
 * @package WPShadow
 */
class Mark_Notification_Read_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_mark_notification_read', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle mark-read request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_dashboard_nonce', 'read' );

		$user_id = get_current_user_id();
		$index   = self::get_post_param( 'index', 'int', -1, true );

		if ( $index < 0 ) {
			self::send_error( __( 'Invalid notification index', 'wpshadow' ) );
		}

		$success = Milestone_Notifier::mark_read( $user_id, $index );

		if ( ! $success ) {
			self::send_error( __( 'Unable to update notification', 'wpshadow' ) );
		}

		self::send_success( array( 'index' => $index ) );
	}
}
