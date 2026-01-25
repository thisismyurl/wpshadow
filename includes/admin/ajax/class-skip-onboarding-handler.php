<?php
/**
 * Skip Onboarding AJAX Handler
 *
 * Handles AJAX requests to skip onboarding process.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

/**
 * AJAX handler for skipping onboarding
 *
 * Action: wp_ajax_wpshadow_skip_onboarding
 * Nonce: wpshadow_onboarding
 * Capability: read
 */
class Skip_Onboarding_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_skip_onboarding', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request to skip onboarding
	 */
	public static function handle(): void {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_onboarding', 'read' );

		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'wpshadow_onboarding_complete', time() );
		update_user_meta( $user_id, 'wpshadow_onboarding_ui_simplified', false );

		self::send_success();
	}
}
