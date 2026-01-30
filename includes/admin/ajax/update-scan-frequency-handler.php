<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Settings\Scan_Frequency_Manager;

/**
 * AJAX Handler: Update Scan Frequency Settings
 *
 * Updates diagnostic scan scheduling configuration.
 *
 * @since 1.2601
 * @package WPShadow
 */
class Update_Scan_Frequency_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX action
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_update_scan_frequency', array( __CLASS__, 'handle' ) );
		add_action( 'wp_ajax_wpshadow_run_scan_now', array( __CLASS__, 'handle_scan_now' ) );
	}

	/**
	 * Handle AJAX request for updating settings
	 */
	public static function handle(): void {
		// Verify security
		self::verify_request( 'wpshadow_scan_frequency_nonce', 'manage_options' );

		// Get scan frequency configuration from form
		$frequency             = self::get_post_param( 'frequency', 'text', 'daily', false );
		$scan_time             = self::get_post_param( 'scan_time', 'text', '02:00', false );
		$run_diagnostics       = (bool) self::get_post_param( 'run_diagnostics', 'text', 'true', false );
		$run_treatments        = (bool) self::get_post_param( 'run_treatments', 'text', 'false', false );
		$email_results         = (bool) self::get_post_param( 'email_results', 'text', 'false', false );
		$scan_on_plugin_update = (bool) self::get_post_param( 'scan_on_plugin_update', 'text', 'true', false );
		$scan_on_theme_update  = (bool) self::get_post_param( 'scan_on_theme_update', 'text', 'true', false );

		// Update settings
		if ( class_exists( Scan_Frequency_Manager::class ) ) {
			Scan_Frequency_Manager::update_setting( 'frequency', $frequency );
			Scan_Frequency_Manager::update_setting( 'scan_time', $scan_time );
			Scan_Frequency_Manager::update_setting( 'run_diagnostics', $run_diagnostics );
			Scan_Frequency_Manager::update_setting( 'run_treatments', $run_treatments );
			Scan_Frequency_Manager::update_setting( 'email_results', $email_results );
			Scan_Frequency_Manager::update_setting( 'scan_on_plugin_update', $scan_on_plugin_update );
			Scan_Frequency_Manager::update_setting( 'scan_on_theme_update', $scan_on_theme_update );

			self::send_success(
				array(
					'message'        => __( 'Scan frequency settings updated successfully', 'wpshadow' ),
					'next_scan_time' => Scan_Frequency_Manager::get_next_scan_time(),
				)
			);
		} else {
			self::send_error( __( 'Scan frequency manager not available', 'wpshadow' ) );
		}
	}

	/**
	 * Handle AJAX request for running scan immediately
	 */
	public static function handle_scan_now(): void {
		// Verify security
		self::verify_request( 'wpshadow_scan_frequency_nonce', 'manage_options' );

		// Run scan
		if ( class_exists( Scan_Frequency_Manager::class ) ) {
			$results = Scan_Frequency_Manager::run_diagnostic_scan();

			self::send_success(
				array(
					'message' => __( 'Diagnostic scan completed successfully', 'wpshadow' ),
					'results' => $results,
				)
			);
		} else {
			self::send_error( __( 'Scan frequency manager not available', 'wpshadow' ) );
		}
	}
}
