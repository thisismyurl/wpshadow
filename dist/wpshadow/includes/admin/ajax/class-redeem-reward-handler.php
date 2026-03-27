<?php
/**
 * AJAX Handler: Redeem Reward
 *
 * Handles redeeming points for rewards (Guardian credits, storage, Pro subscription).
 *
 * Action: wp_ajax_wpshadow_redeem_reward
 * Nonce: wpshadow_gamification
 * Capability: read
 *
 * @package WPShadow
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Gamification\Reward_System;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redeem Reward Handler
 */
class Redeem_Reward_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_redeem_reward', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request
	 *
	 * @since 1.6093.1200
	 * @return void Dies with JSON response.
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_gamification', 'read' );

		$user_id   = get_current_user_id();
		$reward_id = self::get_post_param( 'reward_id', 'key', '', true );

		$result = Reward_System::redeem( $user_id, $reward_id );

		if ( $result['success'] ) {
			self::send_success(
				array(
					'message' => $result['message'],
					'reward'  => $result['reward'] ?? array(),
				)
			);
		} else {
			self::send_error( $result['message'] ?? __( 'Unable to redeem reward.', 'wpshadow' ) );
		}
	}
}
