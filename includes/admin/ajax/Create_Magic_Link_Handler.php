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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Create_Magic_Link_Handler extends AJAX_Handler_Base {

	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_create_magic_link', array( __CLASS__, 'handle' ) );
	}

	public static function handle(): void {
		self::verify_request( 'wpshadow_magic_link_nonce', 'manage_options', 'nonce' );

		$developer_name  = self::get_post_param( 'developer_name', 'text', '', true );
		$developer_email = self::get_post_param( 'developer_email', 'email', '', true );
		$duration_hours  = self::get_post_param( 'duration', 'int', 24 );

		if ( ! is_email( $developer_email ) ) {
			self::send_error( __( 'Invalid email address.', 'wpshadow' ) );
		}

		$token      = wp_generate_password( 32, false );
		$created_at = current_time( 'timestamp' );
		$expires_at = $created_at + ( $duration_hours * HOUR_IN_SECONDS );

		$magic_links           = Options_Manager::get_array( 'wpshadow_magic_links', array() );
		$magic_links[ $token ] = array(
			'developer_name'  => $developer_name,
			'developer_email' => $developer_email,
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
