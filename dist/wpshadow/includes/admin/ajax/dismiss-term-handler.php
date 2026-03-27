<?php
/**
 * Dismiss Terminology Term AJAX Handler
 *
 * Handles AJAX requests to dismiss terminology explanations.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

/**
 * AJAX handler for dismissing terminology terms
 *
 * Action: wp_ajax_wpshadow_dismiss_term
 * Nonce: wpshadow_onboarding
 * Capability: read
 */
class Dismiss_Term_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_dismiss_term', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request to dismiss terminology term
	 */
	public static function handle(): void {
		// Verify nonce and capability
		self::verify_request( 'wpshadow_onboarding', 'read' );

		$user_id = get_current_user_id();
		$term    = self::get_post_param( 'term', '' );

		if ( empty( $term ) ) {
			self::send_error( __( 'Invalid term', 'wpshadow' ) );
		}

		$dismissed   = get_user_meta( $user_id, 'wpshadow_onboarding_dismissed_terms', true ) ?: array();
		$dismissed[] = $term;
		update_user_meta( $user_id, 'wpshadow_onboarding_dismissed_terms', array_unique( $dismissed ) );

		self::send_success();
	}
}
