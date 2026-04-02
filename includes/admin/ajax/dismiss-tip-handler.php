<?php
/**
 * Dismiss Tip AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dismiss_Tip_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX hooks for tip dismissals.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_dismiss_tip', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle tip dismissal requests.
	 *
	 * @since 1.6093.1200
	 * @return void Sends JSON response and exits.
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_tip_dismiss', 'read', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			self::send_error( __( 'User not authenticated.', 'wpshadow' ) );
		}

		$tip_id = self::get_post_param( 'tip_id', 'key', '', true );

		$prefs = \wpshadow_get_user_tip_prefs( $user_id );
		if ( ! isset( $prefs['dismissed_tips'] ) ) {
			$prefs['dismissed_tips'] = array();
		}

		if ( ! in_array( $tip_id, $prefs['dismissed_tips'], true ) ) {
			$prefs['dismissed_tips'][] = $tip_id;
		}

		\wpshadow_save_user_tip_prefs( $user_id, $prefs );
		self::send_success(
			array(
				'message' => __( 'Tip dismissed.', 'wpshadow' ),
				'tip_id'  => $tip_id,
			)
		);
	}
}
