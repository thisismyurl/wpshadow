<?php
/**
 * AJAX Security Trait
 *
 * Provides reusable AJAX security verification methods to eliminate duplicate
 * security checks across AJAX handlers.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73003
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait WPSHADOW_Ajax_Security
 *
 * Centralized AJAX request verification for security and permissions.
 */
trait WPSHADOW_Ajax_Security {
	/**
	 * Verify AJAX request with nonce and capability check.
	 *
	 * Terminates execution with JSON error if verification fails.
	 *
	 * @param string $nonce_action Nonce action to verify.
	 * @param string $capability   Required user capability (default: 'manage_options').
	 * @return void
	 */
	protected function verify_ajax_request( string $nonce_action, string $capability = 'manage_options' ): void {
		check_ajax_referer( $nonce_action, 'nonce' );

		if ( ! current_user_can( $capability ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Insufficient permissions', 'plugin-wpshadow' ) ),
				403
			);
		}
	}

	/**
	 * Verify AJAX request with multisite-aware capability check.
	 *
	 * Automatically determines if running in network admin context and applies
	 * appropriate capability check. Terminates execution with JSON error if verification fails.
	 *
	 * @param string $nonce_action Nonce action to verify.
	 * @param string $site_cap     Capability required on single site.
	 * @param string $network_cap  Capability required on network admin (defaults to $site_cap).
	 * @return array{network_scope:bool} Context information.
	 */
	protected function verify_ajax_request_multisite( string $nonce_action, string $site_cap, string $network_cap = '' ): array {
		check_ajax_referer( $nonce_action, 'nonce' );

		$network_scope = is_multisite() && is_network_admin();
		$cap           = $network_scope ? ( $network_cap ?: $site_cap ) : $site_cap;

		if ( ! current_user_can( $cap ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ),
				403
			);
		}

		return array( 'network_scope' => $network_scope );
	}

	/**
	 * Verify AJAX request for logged-in users only.
	 *
	 * Checks nonce and verifies user is authenticated.
	 * Terminates execution with JSON error if verification fails.
	 *
	 * @param string $nonce_action Nonce action to verify.
	 * @return int User ID of authenticated user.
	 */
	protected function verify_ajax_request_authenticated( string $nonce_action ): int {
		check_ajax_referer( $nonce_action, 'nonce' );

		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			wp_send_json_error(
				array( 'message' => __( 'You must be logged in to perform this action.', 'plugin-wpshadow' ) ),
				401
			);
		}

		return $user_id;
	}
}
