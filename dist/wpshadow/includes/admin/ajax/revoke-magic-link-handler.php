<?php

/**
 * Revoke Magic Link AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Options_Manager;
use WPShadow\Core\Security_Hardening;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Revoke_Magic_Link_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hooks for magic-link revocation.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_revoke_magic_link', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle magic-link revocation requests.
	 *
	 * @since 0.6093.1200
	 * @return void Sends JSON response and exits.
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_magic_link_nonce', 'manage_options', 'nonce' );

		$token_hash = self::get_post_param( 'token', 'key', '', true );

		$magic_links = Options_Manager::get_array( 'wpshadow_magic_links', array() );
		if ( isset( $magic_links[ $token_hash ] ) ) {
			unset( $magic_links[ $token_hash ] );
			update_option( 'wpshadow_magic_links', $magic_links );
			self::send_success( array( 'message' => __( 'Magic link revoked successfully.', 'wpshadow' ) ) );
		} else {
			self::send_error( __( 'Magic link not found.', 'wpshadow' ) );
		}
	}
}
