<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Admin\Pages\Data_Retention_Manager;

/**
 * AJAX Handler: Update Data Retention Settings
 *
 * Updates data retention policies and cleanup configuration.
 *
 * @since 1.6030
 * @package WPShadow
 */
class Update_Data_Retention_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX action
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_update_data_retention', array( __CLASS__, 'handle' ) );
		add_action( 'wp_ajax_wpshadow_run_data_cleanup_now', array( __CLASS__, 'handle_cleanup_now' ) );
	}

	/**
	 * Handle AJAX request for updating settings
	 */
	public static function handle(): void {
		// Verify security
		self::verify_request( 'wpshadow_retention_settings_nonce', 'manage_options' );

		// Get setting key and value
		$setting_key = self::get_post_param( 'setting_key', 'text', '', true );
		$value       = self::get_post_param( 'value', 'raw', '', false );

		// Update setting
		if ( class_exists( Data_Retention_Manager::class ) ) {
			$result = Data_Retention_Manager::update_setting( $setting_key, $value );

			if ( $result ) {
				self::send_success(
					array(
						'message'     => __( 'Data retention setting updated successfully', 'wpshadow' ),
						'setting_key' => $setting_key,
					)
				);
			} else {
				self::send_error( __( 'Failed to update data retention setting', 'wpshadow' ) );
			}
		} else {
			self::send_error( __( 'Data retention manager not available', 'wpshadow' ) );
		}
	}

	/**
	 * Handle AJAX request for running cleanup immediately
	 */
	public static function handle_cleanup_now(): void {
		// Verify security
		self::verify_request( 'wpshadow_retention_settings_nonce', 'manage_options' );

		// Run cleanup
		if ( class_exists( Data_Retention_Manager::class ) ) {
			$results = Data_Retention_Manager::run_cleanup();

			self::send_success(
				array(
					'message' => __( 'Data cleanup completed successfully', 'wpshadow' ),
					'results' => $results,
				)
			);
		} else {
			self::send_error( __( 'Data retention manager not available', 'wpshadow' ) );
		}
	}
}
