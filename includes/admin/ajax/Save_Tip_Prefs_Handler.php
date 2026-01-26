<?php
/**
 * Save Tip Preferences AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Form_Param_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Save_Tip_Prefs_Handler extends AJAX_Handler_Base {
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_save_tip_prefs', array( __CLASS__, 'handle' ) );
	}

	public static function handle(): void {
		self::verify_request( 'wpshadow_tip_prefs', 'read', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			self::send_error( __( 'User not authenticated.', 'wpshadow' ) );
		}

		$disabled_categories = Form_Param_Helper::post_multiple( 'disabled_categories', 'key', array() );

		$prefs = array(
			'disabled_categories' => $disabled_categories,
		);

		\wpshadow_save_user_tip_prefs( $user_id, $prefs );

		// Log activity (#565: Activity Logging Expansion)
		Activity_Logger::log(
			'user_preferences_changed',
			sprintf(
				__( 'Tip preferences updated: %d categories disabled', 'wpshadow' ),
				count( $disabled_categories )
			),
			'settings',
			array( 'disabled_categories' => $disabled_categories )
		);

		self::send_success( array( 'message' => __( 'Tip preferences saved.', 'wpshadow' ) ) );
	}
}
