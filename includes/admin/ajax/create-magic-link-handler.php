<?php

/**
 * Create Magic Link AJAX Handler
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

class Create_Magic_Link_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hooks for magic-link creation.
	 *
	 * @since  1.6047.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_create_magic_link', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle magic-link creation requests.
	 *
	 * @since 1.6047.1200
	 * @return void Sends JSON response and exits.
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_magic_link_nonce', 'manage_options', 'nonce' );

		// Support both old (developer_*) and new (user_*) parameter names for backward compatibility
		$user_name = self::get_post_param( 'user_name', 'text', '' );
		if ( empty( $user_name ) ) {
			$user_name = self::get_post_param( 'developer_name', 'text', '', true );
		}

		$user_email = self::get_post_param( 'user_email', 'email', '' );
		if ( empty( $user_email ) ) {
			$user_email = self::get_post_param( 'developer_email', 'email', '', true );
		}

		$user_role      = self::get_post_param( 'user_role', 'text', 'editor' );
		$duration_hours = self::get_post_param( 'duration', 'int', 24 );

		if ( ! is_email( $user_email ) ) {
			self::send_error( __( 'Invalid email address.', 'wpshadow' ) );
		}

		// Validate user role
		$valid_roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );
		if ( ! in_array( $user_role, $valid_roles, true ) ) {
			$user_role = 'editor'; // Default to editor if invalid role
		}

		// Generate random token for URL (stored only once)
		$token      = wp_generate_password( 32, false );
		$created_at = current_time( 'timestamp' );
		$expires_at = $created_at + ( $duration_hours * HOUR_IN_SECONDS );

		// Hash token before storage (security best practice)
		$token_hash = Security_Hardening::hash_token( $token );

		$magic_links                = Options_Manager::get_array( 'wpshadow_magic_links', array() );
		$magic_links[ $token_hash ] = array(
			'user_name'       => $user_name,
			'user_email'      => $user_email,
			'user_role'       => $user_role,
			'developer_name'  => $user_name, // Backward compatibility
			'developer_email' => $user_email, // Backward compatibility
			'created_at'      => $created_at,
			'expires_at'      => $expires_at,
		);
		update_option( 'wpshadow_magic_links', $magic_links );

		$magic_link_url = add_query_arg(
			array( 'wpshadow_magic_link' => $token ),
			home_url()
		);

		self::send_success(
			array(
				'message'    => __( 'Magic link generated successfully.', 'wpshadow' ),
				'magic_link' => $magic_link_url,
				'token'      => $token,
				'expires_at' => wp_date( get_option( 'date_format', 'Y-m-d' ) . ' ' . get_option( 'time_format', 'H:i:s' ), $expires_at ),
			)
		);
	}
}
