<?php
/**
 * Save Dashboard Preferences AJAX Handler
 *
 * Handles AJAX requests to save user dashboard customization preferences.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Dashboard_Customization;

/**
 * AJAX handler for saving dashboard preferences
 *
 * Action: wp_ajax_wpshadow_save_dashboard_prefs
 * Nonce: wpshadow_admin_nonce
 * Capability: manage_options
 */
class Save_Dashboard_Prefs_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_save_dashboard_prefs', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request to save dashboard preferences
	 */
	public static function handle(): void {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_admin_nonce', 'manage_options' );

		// Get and validate preferences
		$prefs = self::get_post_array_param( 'prefs', 'raw', array() );

		// Sanitize category names
		$sanitized_prefs = array();
		foreach ( $prefs as $category => $value ) {
			$category = sanitize_key( $category );

			if ( '' !== $category && is_array( $value ) ) {
				$sanitized_prefs[ $category ] = array(
					'visible' => isset( $value['visible'] ) ? rest_sanitize_boolean( $value['visible'] ) : true,
					'pinned'  => isset( $value['pinned'] ) ? rest_sanitize_boolean( $value['pinned'] ) : false,
				);
			}
		}

		// Save preferences
		$success = Dashboard_Customization::save_user_preferences( $sanitized_prefs );

		if ( $success ) {
			self::send_success(
				array(
					'message' => __( 'Dashboard preferences saved successfully', 'wpshadow' ),
					'prefs'   => $sanitized_prefs,
				)
			);
		} else {
			self::send_error( __( 'Failed to save dashboard preferences', 'wpshadow' ) );
		}
	}
}
