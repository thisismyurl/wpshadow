<?php
/**
 * Save Onboarding Preferences AJAX Handler
 *
 * Handles AJAX requests to save user onboarding preferences.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

/**
 * AJAX handler for saving onboarding preferences
 *
 * Action: wp_ajax_wpshadow_save_onboarding
 * Nonce: wpshadow_onboarding
 * Capability: read
 */
class Save_Onboarding_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_save_onboarding', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request to save onboarding preferences
	 */
	public static function handle(): void {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_onboarding', 'read' );

		$user_id       = get_current_user_id();
		$platform      = self::get_post_param( 'platform', 'text', '' );
		$comfort_level = self::get_post_param( 'comfort_level', 'text', '' );
		$config        = isset( $_POST['config'] ) ? (array) $_POST['config'] : array();
		$privacy       = isset( $_POST['privacy'] ) ? (array) $_POST['privacy'] : array();

		// Validate platform
		$valid_platforms = array( 'wordpress', 'word', 'google-docs', 'wix', 'squarespace', 'moodle', 'notion', 'none' );
		if ( ! in_array( $platform, $valid_platforms, true ) ) {
			self::send_error( __( 'Invalid platform selected', 'wpshadow' ) );
		}

		// Validate comfort level
		$valid_comfort = array( 'learning', 'comfortable', 'expert' );
		if ( ! in_array( $comfort_level, $valid_comfort, true ) ) {
			self::send_error( __( 'Invalid comfort level selected', 'wpshadow' ) );
		}

		// Save preferences
		update_user_meta( $user_id, 'wpshadow_onboarding_platform', $platform );
		update_user_meta( $user_id, 'wpshadow_onboarding_comfort_level', $comfort_level );
		update_user_meta( $user_id, 'wpshadow_onboarding_complete', time() );

		// Save configuration preferences
		$config_data = array(
			'auto_scan'          => ! empty( $config['auto_scan'] ),
			'show_tips'          => ! empty( $config['show_tips'] ),
			'track_improvements' => ! empty( $config['track_improvements'] ),
		);
		update_user_meta( $user_id, 'wpshadow_config_preferences', $config_data );

		// Save privacy preferences
		$privacy_data = array(
			'email_critical'    => ! empty( $privacy['email_critical'] ),
			'email_weekly'      => ! empty( $privacy['email_weekly'] ),
			'share_diagnostics' => ! empty( $privacy['share_diagnostics'] ),
			'newsletter'        => ! empty( $privacy['newsletter'] ),
			'newsletter_email'  => ! empty( $privacy['newsletter_email'] ) ? sanitize_email( $privacy['newsletter_email'] ) : '',
		);
		update_user_meta( $user_id, 'wpshadow_privacy_preferences', $privacy_data );

		// Set UI simplification based on platform
		$simplified = ( 'WordPress' !== $platform );
		update_user_meta( $user_id, 'wpshadow_onboarding_ui_simplified', $simplified );

		// Track KPI
		if ( class_exists( '\WPShadow\Core\KPI_Tracker' ) ) {
			\WPShadow\Core\KPI_Tracker::record_custom_event(
				'onboarding_completed',
				array(
					'platform'      => $platform,
					'comfort_level' => $comfort_level,
					'config'        => $config_data,
				)
			);
		}

		// Fire action for Pro module integration
		do_action( 'wpshadow_onboarding_completed', $user_id, $platform, $comfort_level, $config_data, $privacy_data );

		// If newsletter requested, trigger subscription
		if ( ! empty( $privacy['newsletter'] ) && ! empty( $privacy_data['newsletter_email'] ) ) {
			do_action(
				'wpshadow_newsletter_subscribe',
				$privacy_data['newsletter_email'],
				array(
					'source'   => 'onboarding',
					'platform' => $platform,
				)
			);
		}

		self::send_success(
			array(
				'message'    => __( 'Great! Your workspace is ready.', 'wpshadow' ),
				'simplified' => $simplified,
			)
		);
	}
}
