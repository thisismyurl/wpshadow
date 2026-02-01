<?php
/**
 * WPShadow Account Registration Handler
 *
 * AJAX handlers for account registration, connection, and management.
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since      1.6032.0000
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\WPShadow_Account_API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Account Registration Handler Class
 *
 * Handles AJAX requests for WPShadow account management.
 *
 * @since 1.6032.0000
 */
class Account_Registration_Handler extends AJAX_Handler_Base {

	/**
	 * Initialize AJAX handlers.
	 *
	 * @since  1.6032.0000
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_ajax_wpshadow_account_register', array( __CLASS__, 'handle_register' ) );
		add_action( 'wp_ajax_wpshadow_account_connect', array( __CLASS__, 'handle_connect' ) );
		add_action( 'wp_ajax_wpshadow_account_disconnect', array( __CLASS__, 'handle_disconnect' ) );
		add_action( 'wp_ajax_wpshadow_account_status', array( __CLASS__, 'handle_check_status' ) );
		add_action( 'wp_ajax_wpshadow_account_sync_services', array( __CLASS__, 'handle_sync_services' ) );
	}

	/**
	 * Handle registration request.
	 *
	 * Creates new WPShadow account.
	 *
	 * @since  1.6032.0000
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_register() {
		self::verify_request( 'wpshadow_account_register', 'manage_options' );

		$email    = self::get_post_param( 'email', 'email', '', true );
		$password = self::get_post_param( 'password', 'text', '', true );

		// Register account.
		$result = WPShadow_Account_API::register( $email, $password );

		if ( $result['success'] ) {
			// Sync services after registration.
			WPShadow_Account_API::sync_services();

			self::send_success( array(
				'message'  => $result['message'],
				'api_key'  => $result['api_key'],
				'services' => $result['services'],
			) );
		} else {
			self::send_error( $result['message'] );
		}
	}

	/**
	 * Handle connect request.
	 *
	 * Connects existing account with API key.
	 *
	 * @since  1.6032.0000
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_connect() {
		self::verify_request( 'wpshadow_account_connect', 'manage_options' );

		$api_key = self::get_post_param( 'api_key', 'text', '', true );

		// Connect account.
		$result = WPShadow_Account_API::connect( $api_key );

		if ( $result['success'] ) {
			// Sync services after connection.
			WPShadow_Account_API::sync_services();

			self::send_success( array(
				'message' => $result['message'],
				'account' => $result['account'],
			) );
		} else {
			self::send_error( $result['message'] );
		}
	}

	/**
	 * Handle disconnect request.
	 *
	 * Disconnects account but keeps local data.
	 *
	 * @since  1.6032.0000
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_disconnect() {
		self::verify_request( 'wpshadow_account_disconnect', 'manage_options' );

		$result = WPShadow_Account_API::disconnect();

		if ( $result['success'] ) {
			self::send_success( array(
				'message' => $result['message'],
			) );
		} else {
			self::send_error( $result['message'] );
		}
	}

	/**
	 * Handle status check request.
	 *
	 * Gets current account status and service information.
	 *
	 * @since  1.6032.0000
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_check_status() {
		self::verify_request( 'wpshadow_account_status', 'manage_options' );

		if ( ! WPShadow_Account_API::is_registered() ) {
			self::send_error( __( 'Not registered with WPShadow', 'wpshadow' ) );
		}

		$account_info = WPShadow_Account_API::get_account_info( true );
		$services     = WPShadow_Account_API::get_services_status();

		if ( is_wp_error( $account_info ) ) {
			self::send_error( $account_info->get_error_message() );
		}

		self::send_success( array(
			'account'  => $account_info,
			'services' => $services,
		) );
	}

	/**
	 * Handle service sync request.
	 *
	 * Syncs account data across Guardian, Vault, and Cloud Services.
	 *
	 * @since  1.6032.0000
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_sync_services() {
		self::verify_request( 'wpshadow_account_sync', 'manage_options' );

		if ( ! WPShadow_Account_API::is_registered() ) {
			self::send_error( __( 'Not registered with WPShadow', 'wpshadow' ) );
		}

		$synced = WPShadow_Account_API::sync_services();

		if ( $synced ) {
			self::send_success( array(
				'message' => __( 'Services synced successfully', 'wpshadow' ),
			) );
		} else {
			self::send_error( __( 'Failed to sync services', 'wpshadow' ) );
		}
	}
}

// Initialize handlers.
Account_Registration_Handler::init();
