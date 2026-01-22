<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Settings\Email_Template_Manager;

/**
 * AJAX Handler: Reset Email Template
 * 
 * Resets customized email template to default.
 * 
 * @since 1.2601
 * @package WPShadow
 */
class Reset_Email_Template_Handler extends AJAX_Handler_Base {
	
	/**
	 * Register AJAX action
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_reset_email_template', [ __CLASS__, 'handle' ] );
	}
	
	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		// Verify security
		self::verify_request( 'wpshadow_email_template_nonce', 'manage_options' );
		
		// Get template key
		$template_key = self::get_post_param( 'template_key', 'text', '', true );
		
		// Reset template
		if ( class_exists( Email_Template_Manager::class ) ) {
			$result = Email_Template_Manager::reset_template( $template_key );
			
			if ( $result ) {
				self::send_success( [
					'message' => __( 'Email template reset to default', 'wpshadow' ),
					'template_key' => $template_key
				] );
			} else {
				self::send_error( __( 'Failed to reset email template', 'wpshadow' ) );
			}
		} else {
			self::send_error( __( 'Email template manager not available', 'wpshadow' ) );
		}
	}
}
