<?php
declare( strict_types=1 );

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Gamification\Leaderboard_Manager;

/**
 * AJAX Handler: Get leaderboard data
 *
 * Action: wp_ajax_wpshadow_get_leaderboard
 * Nonce: wpshadow_dashboard_nonce
 * Capability: read
 *
 * @package WPShadow
 */
class Get_Leaderboard_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_get_leaderboard', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle leaderboard request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_dashboard_nonce', 'read' );

		$limit = self::get_post_param( 'limit', 'int', 10 );
		$limit = max( 3, min( 50, $limit ) ); // keep reasonable bounds

		$leaderboard = Leaderboard_Manager::get_top_achievers( $limit );

		self::send_success(
			array(
				'leaderboard' => $leaderboard,
			)
		);
	}
}
