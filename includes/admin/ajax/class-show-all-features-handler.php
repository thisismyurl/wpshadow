<?php
/**
 * Show All Features AJAX Handler
 *
 * Handles AJAX requests to graduate from simplified UI.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

/**
 * AJAX handler for showing all features (graduating)
 *
 * Action: wp_ajax_wpshadow_show_all_features
 * Nonce: wpshadow_onboarding
 * Capability: read
 */
class Show_All_Features_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_show_all_features', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request to show all features
	 */
	public static function handle(): void {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_onboarding', 'read' );

		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'wpshadow_onboarding_ui_simplified', false );

		// Track graduation KPI
		if ( class_exists( '\WPShadow\Core\KPI_Tracker' ) ) {
			// Get action count if method exists
			$action_count = 0;
			if ( class_exists( '\WPShadow\Onboarding\Onboarding_Manager' ) ) {
				$action_count = \WPShadow\Onboarding\Onboarding_Manager::get_action_count( $user_id );
			}

			\WPShadow\Core\KPI_Tracker::record_custom_event(
				'onboarding_graduated',
				array(
					'action_count' => $action_count,
				)
			);
		}

		self::send_success();
	}
}
