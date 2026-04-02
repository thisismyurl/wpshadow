<?php
/**
 * Consent Preferences AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Activity_Logger;
use WPShadow\Privacy\First_Run_Consent;
use WPShadow\Privacy\Consent_Preferences;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX handler for saving and dismissing consent preferences.
 */
class Consent_Preferences_Handler extends AJAX_Handler_Base {
	/**
	 * Register AJAX hooks.
	 */
	public static function register(): void {
		// SECURITY: Only logged-in users can manage consent (removed nopriv hooks).
		add_action( 'wp_ajax_wpshadow_save_consent', array( __CLASS__, 'handle_save' ) );
		add_action( 'wp_ajax_wpshadow_dismiss_consent', array( __CLASS__, 'handle_dismiss' ) );
	}

	/**
	 * Save consent preferences for the current admin user.
	 */
	public static function handle_save(): void {
		// Verify nonce without dying - more graceful error handling
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpshadow_consent' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed. Please refresh the page and try again.', 'wpshadow' ),
				)
			);
		}

		// Verify user is logged in
		if ( ! is_user_logged_in() ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please log in to save your preferences.', 'wpshadow' ),
				)
			);
		}

		$current_user = get_current_user_id();
		$telemetry    = self::get_post_param( 'telemetry', 'bool', false, false );

		First_Run_Consent::save_consent(
			$current_user,
			array(
				'anonymized_telemetry' => (bool) $telemetry,
			)
		);

		Activity_Logger::log(
			'consent_saved',
			'Consent preferences updated',
			'privacy',
			array( 'telemetry' => (bool) $telemetry )
		);

		self::send_success( array( 'message' => __( 'Consent saved. Thank you for confirming your preferences.', 'wpshadow' ) ) );
	}

	/**
	 * Dismiss consent prompt with increasing delay.
	 */
	public static function handle_dismiss(): void {
		// Verify nonce without dying - more graceful error handling
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpshadow_consent' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed. Please refresh the page and try again.', 'wpshadow' ),
				)
			);
		}

		// Verify user is logged in
		if ( ! is_user_logged_in() ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please log in to dismiss this prompt.', 'wpshadow' ),
				)
			);
		}

		$current_user = get_current_user_id();
		$duration     = First_Run_Consent::dismiss_consent( $current_user );
		
		// Format duration for user message
		$duration_text = '';
		if ( $duration <= DAY_IN_SECONDS ) {
			$duration_text = __( '1 day', 'wpshadow' );
		} elseif ( $duration <= 3 * DAY_IN_SECONDS ) {
			$duration_text = __( '3 days', 'wpshadow' );
		} elseif ( $duration <= WEEK_IN_SECONDS ) {
			$duration_text = __( '1 week', 'wpshadow' );
		} elseif ( $duration <= 2 * WEEK_IN_SECONDS ) {
			$duration_text = __( '2 weeks', 'wpshadow' );
		} else {
			$duration_text = __( '1 month', 'wpshadow' );
		}

		Activity_Logger::log(
			'settings_changed',
			sprintf( 'Consent prompt dismissed for %s', $duration_text ),
			'privacy'
		);

		self::send_success(
			array(
				/* translators: %s: duration text (e.g., "1 week", "1 month") */
				'message' => sprintf( __( 'Privacy notice dismissed for %s.', 'wpshadow' ), $duration_text ),
			)
		);
	}
}
