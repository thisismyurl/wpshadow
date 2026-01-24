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
		add_action( 'wp_ajax_wpshadow_save_consent', array( __CLASS__, 'handle_save' ) );
		add_action( 'wp_ajax_wpshadow_dismiss_consent', array( __CLASS__, 'handle_dismiss' ) );
	}

	/**
	 * Save consent preferences for the current admin user.
	 */
	public static function handle_save(): void {
		self::verify_request( 'wpshadow_consent', 'manage_options' );

		$current_user = get_current_user_id();
		$telemetry    = self::get_post_param( 'telemetry', 'bool', false, false );

		First_Run_Consent::save_consent( $current_user, array(
			'anonymized_telemetry' => (bool) $telemetry,
		) );

		Activity_Logger::log(
			'consent_saved',
			'Consent preferences updated',
			'privacy',
			array( 'telemetry' => (bool) $telemetry )
		);

		self::send_success( array( 'message' => __( 'Consent saved. Thank you for confirming your preferences.', 'wpshadow' ) ) );
	}

	/**
	 * Dismiss consent prompt for 30 days.
	 */
	public static function handle_dismiss(): void {
		self::verify_request( 'wpshadow_consent', 'manage_options' );

		$current_user = get_current_user_id();
		First_Run_Consent::dismiss_consent( $current_user );

		Activity_Logger::log(
			'settings_changed',
			'Consent prompt dismissed for 30 days',
			'privacy'
		);

		self::send_success( array( 'message' => __( 'Consent prompt snoozed for 30 days.', 'wpshadow' ) ) );
	}
}
