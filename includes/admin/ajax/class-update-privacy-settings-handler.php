<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Settings\Privacy_Settings_Manager;

/**
 * AJAX Handler: Update Privacy Settings
 * 
 * Updates GDPR compliance and privacy configuration.
 * 
 * @since 1.2601
 * @package WPShadow
 */
class Update_Privacy_Settings_Handler extends AJAX_Handler_Base {
	
	/**
	 * Register AJAX action
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_update_privacy_settings', [ __CLASS__, 'handle' ] );
	}
	
	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		// Verify security
		self::verify_request( 'wpshadow_privacy_settings_nonce', 'manage_options' );
		
		// Get setting key and value
		$setting_key = self::get_post_param( 'setting_key', 'text', '', true );
		$value = self::get_post_param( 'value', 'raw', '', false );
		
		// Update setting
		if ( class_exists( Privacy_Settings_Manager::class ) ) {
			$result = Privacy_Settings_Manager::update_setting( $setting_key, $value );
			
			if ( $result ) {
				self::send_success( [
					'message' => __( 'Privacy setting updated successfully', 'wpshadow' ),
					'setting_key' => $setting_key
				] );
			} else {
				self::send_error( __( 'Failed to update privacy setting', 'wpshadow' ) );
			}
		} else {
			self::send_error( __( 'Privacy settings manager not available', 'wpshadow' ) );
		}
	}
}
