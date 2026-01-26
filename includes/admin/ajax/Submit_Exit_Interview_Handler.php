<?php
/**
 * AJAX Handler: Submit Exit Interview
 *
 * Handles submission of exit interview responses when users deactivate the plugin.
 *
 * Action: wp_ajax_wpshadow_submit_exit_interview
 * Nonce: wpshadow_exit_interview
 * Capability: activate_plugins (user must be able to deactivate plugins)
 *
 * Philosophy:
 * - Commandment #1 (Helpful Neighbor) - Listen to user feedback
 * - Commandment #10 (Beyond Pure) - Privacy first, consent required
 *
 * @since   1.2601.2148
 * @package WPShadow\Admin\Ajax
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Engagement\Exit_Interview;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Submit Exit Interview Handler
 */
class Submit_Exit_Interview_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 *
	 * @since  1.2601.2148
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_submit_exit_interview', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the AJAX request
	 *
	 * @since  1.2601.2148
	 * @return void Dies after sending JSON response.
	 */
	public static function handle(): void {
		try {
			// Verify nonce and capability
			self::verify_request( 'wpshadow_exit_interview', 'activate_plugins' );

			// Get and sanitize parameters
			$reason        = self::get_post_param( 'reason', 'text', '', true );
			$details       = self::get_post_param( 'details', 'textarea', '' );
			$allow_contact = self::get_post_param( 'allow_contact', 'bool', false );

			// Validate reason
			$valid_reasons = array(
				'not_working',
				'too_complex',
				'found_better',
				'temporary',
				'performance',
				'missing_features',
				'switching_site',
				'other',
			);

			if ( ! in_array( $reason, $valid_reasons, true ) ) {
				self::send_error( __( 'Invalid reason provided', 'wpshadow' ) );
			}

			// Prepare data
			$data = array(
				'reason'        => $reason,
				'details'       => $details,
				'allow_contact' => $allow_contact,
			);

			// Save the response
			$result = Exit_Interview::save_response( $data );

			if ( ! $result ) {
				self::send_error( __( 'Failed to save your feedback. We apologize for the inconvenience.', 'wpshadow' ) );
			}

			// Send success response
			self::send_success(
				array(
					'message' => __( 'Thank you for your feedback! We truly appreciate it.', 'wpshadow' ),
				)
			);

		} catch ( \Exception $e ) {
			\WPShadow\Core\Error_Handler::log_error(
				'Exit interview submission error: ' . $e->getMessage(),
				$e
			);
			self::send_error( __( 'An error occurred. Please try again.', 'wpshadow' ) );
		}
	}
}
