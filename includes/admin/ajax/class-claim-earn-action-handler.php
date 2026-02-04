<?php
/**
 * AJAX Handler: Claim Earn Action
 *
 * Handles claiming points for optional actions like reviews and shares.
 *
 * Action: wp_ajax_wpshadow_claim_earn_action
 * Nonce: wpshadow_gamification
 * Capability: read
 *
 * @package WPShadow
 * @since   1.6004.0400
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Gamification\Earn_Actions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Claim Earn Action Handler
 */
class Claim_Earn_Action_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 *
	 * @since 1.6004.0400
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_claim_earn_action', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request
	 *
	 * @since 1.6004.0400
	 * @return void Dies with JSON response.
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_gamification', 'read' );

		$user_id   = get_current_user_id();
		$action_id = self::get_post_param( 'action_id', 'key', '', true );

		$result = Earn_Actions::claim( $user_id, $action_id );

		if ( $result['success'] ) {
			self::send_success(
				array(
					'message' => $result['message'],
					'points'  => $result['points'] ?? 0,
				)
			);
		} else {
			self::send_error( $result['message'] ?? __( 'Unable to claim points.', 'wpshadow' ) );
		}
	}
}
