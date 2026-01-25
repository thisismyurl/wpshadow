<?php
/**
 * Send Executive Report AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\KPI_Advanced_Features;

/**
 * AJAX Handler: Send Executive Email Report
 *
 * Action: wp_ajax_wpshadow_send_executive_report
 * Nonce: wpshadow_admin_nonce
 * Capability: manage_options
 */
class Send_Executive_Report_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_send_executive_report', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		// Verify security
		self::verify_request( 'wpshadow_admin_nonce', 'manage_options' );

		// Get email parameter (optional, defaults to admin_email)
		$email = self::get_post_param( 'email', 'email', get_option( 'admin_email', '' ), false );

		// Send report
		$result = KPI_Advanced_Features::send_executive_report( $email, 'monthly' );

		if ( $result ) {
			self::send_success(
				array(
					'message' => sprintf(
						__( 'Executive report sent successfully to %s', 'wpshadow' ),
						$email
					),
				)
			);
		} else {
			self::send_error( __( 'Failed to send executive report. Check email configuration.', 'wpshadow' ) );
		}
	}
}
