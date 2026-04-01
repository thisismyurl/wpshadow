<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Admin\Pages\Email_Template_Manager;

/**
 * AJAX Handler: Save Email Template
 *
 * Saves customized email templates with sanitization.
 *
 * @since 0.6093.1200
 * @package WPShadow
 */
class Save_Email_Template_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX action
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_save_email_template', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		// Verify security
		self::verify_request( 'wpshadow_email_template_nonce', 'manage_options' );

		// Get and validate template key
		$template_key = self::get_post_param( 'template_key', 'text', '', true );
		$html_content = self::get_post_param( 'html_content', 'raw', '', true );
		$text_content = self::get_post_param( 'text_content', 'text', '', true );

		// Save template
		if ( class_exists( Email_Template_Manager::class ) ) {
			$result = Email_Template_Manager::save_template( $template_key, $html_content, $text_content );

			if ( $result ) {
				self::send_success(
					array(
						'message'      => __( 'Email template saved successfully', 'wpshadow' ),
						'template_key' => $template_key,
					)
				);
			} else {
				self::send_error( __( 'Failed to save email template', 'wpshadow' ) );
			}
		} else {
			self::send_error( __( 'Email template manager not available', 'wpshadow' ) );
		}
	}
}
