<?php
declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Cloud\Registration_Manager;
use WPShadow\Core\Command_Base;

/**
 * Register Site Command
 * 
 * AJAX endpoint: wp_ajax_wpshadow_register_cloud
 * 
 * Handles user registration with cloud service.
 * Endpoint is admin-only and requires nonce verification.
 */
class Register_Cloud_Command extends Command_Base {
	
	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_register_cloud', [ __CLASS__, 'handle' ] );
	}
	
	/**
	 * Handle registration AJAX request
	 * 
	 * POST parameters:
	 * - nonce: wpshadow_register_nonce
	 * - email: (optional) Registration email (defaults to admin email)
	 */
	public static function handle(): void {
		// Security checks (admin-only)
		self::verify_request( 'wpshadow_register_nonce', 'manage_options' );
		
		// Get and sanitize email
		$email = self::get_post_param( 'email', 'email', '', false );
		if ( empty( $email ) ) {
			$email = get_option( 'admin_email' );
		}
		
		// Register with cloud service
		$result = Registration_Manager::register_user( $email );
		
		if ( isset( $result['error'] ) ) {
			self::send_error( $result['error'] );
			return;
		}
		
		self::send_success( [
			'message'              => $result['message'] ?? __( 'Registration successful', 'wpshadow' ),
			'cloud_dashboard_url'  => $result['cloud_dashboard_url'] ?? '',
			'site_id'              => $result['site_id'] ?? '',
		] );
	}
}
