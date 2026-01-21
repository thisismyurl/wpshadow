<?php
declare( strict_types=1 );

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

/**
 * AJAX Handler: Dashboard First Scan
 * 
 * Action: wp_ajax_wpshadow_first_scan
 * Nonce: wpshadow_first_scan_nonce
 * Capability: manage_options
 * 
 * Philosophy: Show value (#9) - Run initial scan to get quick baseline
 * 
 * @package WPShadow
 */
class First_Scan_Handler extends AJAX_Handler_Base {
	
	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_first_scan', array( __CLASS__, 'handle' ) );
	}
	
	/**
	 * Handle first scan AJAX request
	 */
	public static function handle(): void {
		// Verify security
		self::verify_request( 'wpshadow_first_scan_nonce', 'manage_options' );
		
		// Record scan start time
		update_option( 'wpshadow_last_quick_scan', time() );
		
		// Log the activity
		if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'diagnostic_run',
				'quick_scan_initiated_first_time',
				'First Quick Scan initiated by user',
				'security'
			);
		}
		
		self::send_success( array(
			'message' => __( 'Quick Scan started. Analyzing your site...', 'wpshadow' ),
			'scan_time' => time(),
		) );
	}
}
