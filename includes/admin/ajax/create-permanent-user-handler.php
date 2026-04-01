<?php
/**
 * Create Permanent User from Magic Link Handler
 *
 * Handles one-click permanent user creation from expired magic links.
 *
 * @package    WPShadow
 * @subpackage Admin\Ajax
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Utils\Magic_Link_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create Permanent User Handler
 *
 * @since 0.6093.1200
 */
class Create_Permanent_User_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_create_permanent_user', array( __CLASS__, 'handle' ) );

		// Also handle via URL parameter for email links
		add_action( 'admin_init', array( __CLASS__, 'handle_url_request' ) );
	}

	/**
	 * Handle AJAX request
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_create_permanent_user', 'manage_options', 'nonce' );

		$token = self::get_post_param( 'token', 'key', '', true );

		$result = Magic_Link_Manager::create_permanent_user( $token );

		if ( $result['success'] ) {
			self::send_success( $result );
		} else {
			self::send_error( $result['message'] );
		}
	}

	/**
	 * Handle URL-based request (from email link)
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function handle_url_request(): void {
		// Check if this is a create permanent user request
		if ( ! isset( $_GET['wpshadow_action'] ) || 'create_permanent_user' !== $_GET['wpshadow_action'] ) {
			return;
		}

		// Check capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to create users.', 'wpshadow' ) );
		}

		// Get and validate parameters
		$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
		$nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';

		if ( empty( $token ) || empty( $nonce ) ) {
			wp_die( esc_html__( 'Invalid request parameters.', 'wpshadow' ) );
		}

		// Verify nonce
		if ( ! wp_verify_nonce( $nonce, 'wpshadow_create_permanent_user_' . $token ) ) {
			wp_die( esc_html__( 'Security check failed.', 'wpshadow' ) );
		}

		// Create the user
		$result = Magic_Link_Manager::create_permanent_user( $token );

		// Redirect with message
		$redirect_url = add_query_arg(
			array(
				'page'                      => 'wpshadow-utilities',
				'tab'                       => 'magic-link-support',
				'permanent_user_created'    => $result['success'] ? '1' : '0',
				'permanent_user_message'    => rawurlencode( $result['message'] ),
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}
}
