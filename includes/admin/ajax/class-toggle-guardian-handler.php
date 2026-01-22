<?php
/**
 * Toggle Guardian AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Activity_Logger;
use WPShadow\Guardian\Guardian_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle Guardian enable/disable toggle
 */
class Toggle_Guardian_Handler extends AJAX_Handler_Base {
	
	/**
	 * Register AJAX hooks
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_toggle_guardian', [ __CLASS__, 'handle' ] );
	}
	
	/**
	 * Handle the toggle request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_toggle_guardian', 'manage_options' );
		
		$enabled = self::get_post_param( 'enabled', 'bool', false, true );
		
		// Get current settings and update enabled status
		$settings = Guardian_Manager::get_settings();
		$settings['enabled'] = $enabled;
		
		// Save updated settings
		$result = Guardian_Manager::update_settings( $settings );
		
		if ( ! $result ) {
			self::send_error( __( 'Failed to update Guardian settings', 'wpshadow' ) );
			return;
		}
		
		// Log activity
		Activity_Logger::log(
			$enabled ? 'guardian_enabled' : 'guardian_disabled',
			sprintf(
				__( 'Guardian %s', 'wpshadow' ),
				$enabled ? __( 'enabled', 'wpshadow' ) : __( 'disabled', 'wpshadow' )
			),
			'monitoring',
			array( 'enabled' => $enabled )
		);
		
		self::send_success( array(
			'message' => $enabled 
				? __( 'Guardian enabled. Automated health monitoring is now active.', 'wpshadow' )
				: __( 'Guardian disabled. Automated health monitoring has been stopped.', 'wpshadow' ),
			'enabled' => $enabled,
		) );
	}
}
