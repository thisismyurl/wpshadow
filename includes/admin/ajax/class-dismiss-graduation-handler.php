<?php
/**
 * Dismiss Graduation Notice AJAX Handler
 *
 * Handles AJAX requests to dismiss graduation notice.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

/**
 * AJAX handler for dismissing graduation notice
 *
 * Action: wp_ajax_wpshadow_dismiss_graduation
 * Nonce: wpshadow_onboarding
 * Capability: read
 */
class Dismiss_Graduation_Handler extends AJAX_Handler_Base {
	
	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_dismiss_graduation', array( __CLASS__, 'handle' ) );
	}
	
	/**
	 * Handle AJAX request to dismiss graduation notice
	 */
	public static function handle(): void {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_onboarding', 'read' );
		
		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'wpshadow_graduation_dismissed', time() );
		
		self::send_success();
	}
}
