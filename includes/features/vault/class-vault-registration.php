<?php
/**
 * WPShadow Vault Registration System
 *
 * Handles user registration for Vault cloud storage.
 * Free tier: 3 backups, 7-day retention.
 *
 * @package    WPShadow
 * @subpackage Vault
 * @since 1.6364
 */

declare(strict_types=1);

namespace WPShadow\Vault;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Settings_Registry;
use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vault_Registration Class
 *
 * Manages Vault account registration, API key exchange, and account verification.
 *
 * @since 1.6364
 */
class Vault_Registration extends Hook_Subscriber_Base {

	/**
	 * Vault API base URL
	 *
	 * @var string
	 */
	const API_BASE_URL = 'https://vault.wpshadow.com/api/v1';

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'wp_ajax_wpshadow_vault_register'      => 'handle_register',
			'wp_ajax_wpshadow_vault_connect'       => 'handle_connect',
			'wp_ajax_wpshadow_vault_disconnect'    => 'handle_disconnect',
			'wp_ajax_wpshadow_vault_check_status'  => 'handle_check_status',
		);
	}

	/**
	 * Get the minimum required version for this feature.
	 *
	 * @since  1.6364
	 * @return string Minimum required version.
	 */
	protected static function get_required_version(): string {
		return '1.6364';
	}

	/**
	 * Initialize registration hooks (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use Vault_Registration::subscribe() instead
	 * @since      1.6030.1835
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Handle registration request
	 *
	 * Creates new Vault account with email/password.
	 *
	 * @since  1.6030.1835
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_register() {
		self::verify_request( 'wpshadow_vault_register', 'manage_options' );

		$email    = self::get_post_param( 'email', 'email', '', true );
		$password = self::get_post_param( 'password', 'text', '', true );
		$site_url = site_url();

		// Validate password strength.
		if ( strlen( $password ) < 8 ) {
			self::send_error( __( 'Password must be at least 8 characters', 'wpshadow' ) );
		}

		// Call Vault API to create account.
		$response = wp_remote_post(
			self::API_BASE_URL . '/register',
			array(
				'body'    => wp_json_encode(
					array(
						'email'    => $email,
						'password' => $password,
						'site_url' => $site_url,
					)
				),
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			self::send_error( __( 'Failed to connect to Vault service', 'wpshadow' ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['success'] ) && $body['success'] ) {
			// Save API key.
			Settings_Registry::set( 'vault_api_key', $body['api_key'] );
			Settings_Registry::set( 'vault_email', $email );

			// Clear tier cache.
		\WPShadow\Core\Cache_Manager::delete( 'vault_tier', 'wpshadow_vault' );

			Activity_Logger::log(
				'vault_registered',
				array(
					'email'    => $email,
					'site_url' => $site_url,
				)
			);

			self::send_success(
				array(
					'message' => __( 'Welcome to WPShadow Vault! You have 3 free backups.', 'wpshadow' ),
					'api_key' => $body['api_key'],
				)
			);
		} else {
			$error_message = $body['message'] ?? __( 'Registration failed', 'wpshadow' );
			self::send_error( $error_message );
		}
	}

	/**
	 * Handle connect with existing API key
	 *
	 * For users who already have a Vault account.
	 *
	 * @since  1.6030.1835
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_connect() {
		self::verify_request( 'wpshadow_vault_connect', 'manage_options' );

		$api_key = self::get_post_param( 'api_key', 'text', '', true );

		// Validate API key with Vault service.
		$is_valid = self::validate_api_key( $api_key );

		if ( $is_valid ) {
			Settings_Registry::set( 'vault_api_key', $api_key );

			// Clear tier cache.
		\WPShadow\Core\Cache_Manager::delete( 'vault_tier', 'wpshadow_vault' );

			// Fetch account info.
			$account_info = self::fetch_account_info( $api_key );
			if ( $account_info ) {
				Settings_Registry::set( 'vault_email', $account_info['email'] );
			}

			Activity_Logger::log( 'vault_connected', array( 'site_url' => site_url() ) );

			self::send_success(
				array(
					'message'      => __( 'Vault connected successfully!', 'wpshadow' ),
					'account_info' => $account_info,
				)
			);
		} else {
			self::send_error( __( 'Invalid API key', 'wpshadow' ) );
		}
	}

	/**
	 * Handle disconnect request
	 *
	 * Removes Vault API key (keeps local backups).
	 *
	 * @since  1.6030.1835
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_disconnect() {
		self::verify_request( 'wpshadow_vault_disconnect', 'manage_options' );

		Settings_Registry::set( 'vault_api_key', '' );
		Settings_Registry::set( 'vault_email', '' );

	\WPShadow\Core\Cache_Manager::delete( 'vault_tier', 'wpshadow_vault' );

		Activity_Logger::log( 'vault_disconnected', array( 'site_url' => site_url() ) );

		self::send_success(
			array(
				'message' => __( 'Vault disconnected. Your local backups are safe.', 'wpshadow' ),
			)
		);
	}

	/**
	 * Handle status check request
	 *
	 * Gets current Vault account status and usage.
	 *
	 * @since  1.6030.1835
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_check_status() {
		self::verify_request( 'wpshadow_vault_status', 'manage_options' );

		$api_key = Settings_Registry::get( 'vault_api_key', '' );

		if ( empty( $api_key ) ) {
			self::send_error( __( 'Not connected to Vault', 'wpshadow' ) );
		}

		$account_info = self::fetch_account_info( $api_key );

		if ( $account_info ) {
			self::send_success( $account_info );
		} else {
			self::send_error( __( 'Failed to fetch account status', 'wpshadow' ) );
		}
	}

	/**
	 * Validate API key with Vault service
	 *
	 * @since  1.6030.1835
	 * @param  string $api_key API key to validate.
	 * @return bool True if valid, false otherwise.
	 */
	private static function validate_api_key( $api_key ) {
		// Check cache first.
		$cache_key = 'vault_key_valid_' . md5( $api_key );
		$cached    = \WPShadow\Core\Cache_Manager::get(
			$cache_key,
			'wpshadow_vault'
		);

		if ( false !== $cached ) {
			return (bool) $cached;
		}

		$response = wp_remote_get(
			self::API_BASE_URL . '/validate',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body    = json_decode( wp_remote_retrieve_body( $response ), true );
		$is_valid = isset( $body['valid'] ) && $body['valid'];

		// Cache for 1 hour.
		\WPShadow\Core\Cache_Manager::set(
			$cache_key,
			$is_valid,
			HOUR_IN_SECONDS,
			'wpshadow_vault'
			);

		return $is_valid;
	}

	/**
	 * Fetch account information from Vault API
	 *
	 * @since  1.6030.1835
	 * @param  string $api_key API key.
	 * @return array|false Account info or false on failure.
	 */
	private static function fetch_account_info( $api_key ) {
		$response = wp_remote_get(
			self::API_BASE_URL . '/account',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['account'] ) ) {
			return $body['account'];
		}

		return false;
	}

	/**
	 * Check if Vault service is available
	 *
	 * @since  1.6030.1835
	 * @return bool True if available, false otherwise.
	 */
	public static function is_service_available() {
		$cache_key = 'vault_service_available';
		$cached    = \WPShadow\Core\Cache_Manager::get( $cache_key, 'wpshadow_vault' );

		if ( false !== $cached ) {
			return (bool) $cached;
		}

		$response = wp_remote_get(
			self::API_BASE_URL . '/status',
			array( 'timeout' => 5 )
		);

		$is_available = ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response );

		// Cache for 5 minutes.
		\WPShadow\Core\Cache_Manager::set(
			$cache_key,
			$is_available,
			5 * MINUTE_IN_SECONDS,
			'wpshadow_vault'
			);

		return $is_available;
	}

	/**
	 * Get registration prompt message
	 *
	 * Explains free tier benefits.
	 *
	 * @since  1.6030.1835
	 * @return string Prompt message HTML.
	 */
	public static function get_registration_prompt() {
		ob_start();
		?>
		<div class="wpshadow-vault-prompt">
			<h3><?php esc_html_e( 'Protect Your Site with WPShadow Vault', 'wpshadow' ); ?></h3>
			<p>
				<?php
				esc_html_e(
					'Get 3 free full-site backups with 7-day retention. No credit card required.',
					'wpshadow'
				);
				?>
			</p>
			<ul class="wpshadow-vault-benefits">
				<li>✅ <?php esc_html_e( '3 free backups included', 'wpshadow' ); ?></li>
				<li>✅ <?php esc_html_e( 'One-click restore', 'wpshadow' ); ?></li>
				<li>✅ <?php esc_html_e( 'Auto-backup before risky changes', 'wpshadow' ); ?></li>
				<li>✅ <?php esc_html_e( 'Local + cloud storage', 'wpshadow' ); ?></li>
			</ul>
			<button class="button button-primary" id="wpshadow-vault-register-btn">
				<?php esc_html_e( 'Get 3 Free Backups', 'wpshadow' ); ?>
			</button>
			<p class="wpshadow-vault-note">
				<?php
				printf(
					/* translators: %s: link to pricing page */
					esc_html__(
						'Need more? %s starting at $9/month.',
						'wpshadow'
					),
					'<a href="https://wpshadow.com/vault/pricing/" target="_blank">' . esc_html__( 'Paid plans', 'wpshadow' ) . '</a>'
				);
				?>
			</p>
		</div>
		<?php
		return ob_get_clean();
	}
}
