<?php
/**
 * Site Registration Handler
 *
 * Handles seamless site registration with newsletter opt-ins and
 * license provisioning.
 *
 * @package WP_Support
 * @since   1.2601.73002
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registration Handler Class
 */
class WPS_Registration {
	/**
	 * Registration endpoint URL
	 */
	private const ENDPOINT = 'https://thisismyurl.com/wp-json/wps/v1/register';

	/**
	 * Initialize registration hooks
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wp_ajax_wps_register_site', array( self::class, 'handle_ajax_registration' ) );
	}

	/**
	 * Handle AJAX registration request
	 *
	 * @return void
	 */
	public static function handle_ajax_registration(): void {
		// Verify nonce.
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wps_register_site' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security verification failed. Please refresh the page and try again.', 'plugin-wp-support-thisismyurl' ),
				)
			);
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to register this site.', 'plugin-wp-support-thisismyurl' ),
				)
			);
		}

		// Validate and sanitize input data.
		$site_name   = isset( $_POST['site_name'] ) ? sanitize_text_field( wp_unslash( $_POST['site_name'] ) ) : '';
		$site_url    = isset( $_POST['site_url'] ) ? esc_url_raw( wp_unslash( $_POST['site_url'] ) ) : '';
		$admin_name  = isset( $_POST['admin_name'] ) ? sanitize_text_field( wp_unslash( $_POST['admin_name'] ) ) : '';
		$admin_email = isset( $_POST['admin_email'] ) ? sanitize_email( wp_unslash( $_POST['admin_email'] ) ) : '';
		$agree_terms = isset( $_POST['agree_terms'] ) ? (bool) $_POST['agree_terms'] : false;

		// Email preferences.
		$opt_in_updates    = isset( $_POST['opt_in_updates'] ) ? (bool) $_POST['opt_in_updates'] : false;
		$opt_in_security   = isset( $_POST['opt_in_security'] ) ? (bool) $_POST['opt_in_security'] : false;
		$opt_in_newsletter = isset( $_POST['opt_in_newsletter'] ) ? (bool) $_POST['opt_in_newsletter'] : false;
		$opt_in_marketing  = isset( $_POST['opt_in_marketing'] ) ? (bool) $_POST['opt_in_marketing'] : false;

		// Validate required fields.
		if ( empty( $site_name ) || empty( $site_url ) || empty( $admin_name ) || empty( $admin_email ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please fill in all required fields.', 'plugin-wp-support-thisismyurl' ),
				)
			);
		}

		// Validate email.
		if ( ! is_email( $admin_email ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please enter a valid email address.', 'plugin-wp-support-thisismyurl' ),
				)
			);
		}

		// Validate terms agreement.
		if ( ! $agree_terms ) {
			wp_send_json_error(
				array(
					'message' => __( 'You must agree to the Terms of Service and Privacy Policy.', 'plugin-wp-support-thisismyurl' ),
				)
			);
		}

		// Prepare registration data.
		$registration_data = array(
			'site_name'         => $site_name,
			'site_url'          => $site_url,
			'admin_name'        => $admin_name,
			'admin_email'       => $admin_email,
			'opt_in_updates'    => $opt_in_updates,
			'opt_in_security'   => $opt_in_security,
			'opt_in_newsletter' => $opt_in_newsletter,
			'opt_in_marketing'  => $opt_in_marketing,
			'agree_terms'       => $agree_terms,
			'wp_version'        => get_bloginfo( 'version' ),
			'plugin_version'    => defined( 'wp_support_VERSION' ) ? wp_support_VERSION : 'unknown',
			'php_version'       => PHP_VERSION,
			'locale'            => get_locale(),
			'suite_id'          => defined( 'WPS_SUITE_ID' ) ? WPS_SUITE_ID : 'unknown',
		);

		// Call registration endpoint.
		$result = self::call_registration_endpoint( $registration_data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				)
			);
		}

		// Store license key if provided.
		if ( ! empty( $result['license_key'] ) ) {
			WPS_License::save_key( $result['license_key'], false );

			// Validate the key immediately.
			$validation = WPS_License::validate_key( $result['license_key'], false );

			if ( 'valid' !== $validation['status'] ) {
				wp_send_json_error(
					array(
						'message' => __( 'Registration successful, but license validation failed. Please contact support.', 'plugin-wp-support-thisismyurl' ),
					)
				);
			}
		}

		// Log the registration event.
		if ( class_exists( '\\WPS\\CoreSupport\\WPS_Activity_Logger' ) ) {
			WPS_Activity_Logger::log(
				'registration',
				sprintf(
					/* translators: %s: admin email */
					__( 'Site registered successfully for %s', 'plugin-wp-support-thisismyurl' ),
					$admin_email
				),
				array(
					'site_url'    => $site_url,
					'admin_email' => $admin_email,
					'opt_ins'     => array(
						'updates'    => $opt_in_updates,
						'security'   => $opt_in_security,
						'newsletter' => $opt_in_newsletter,
						'marketing'  => $opt_in_marketing,
					),
				)
			);
		}

		// Success response.
		wp_send_json_success(
			array(
				'message'  => __( 'Registration successful! You will now receive updates and can access premium features.', 'plugin-wp-support-thisismyurl' ),
				'redirect' => admin_url( 'admin.php?page=wp-support' ),
			)
		);
	}

	/**
	 * Call the remote registration endpoint
	 *
	 * @param array $data Registration data.
	 * @return array|\WP_Error Response data or WP_Error on failure.
	 */
	private static function call_registration_endpoint( array $data ) {
		$args = array(
			'method'     => 'POST',
			'timeout'    => 15,
			'sslverify'  => true,
			'user-agent' => 'WPS-Core-Registration/' . ( defined( 'wp_support_VERSION' ) ? wp_support_VERSION : 'dev' ),
			'body'       => wp_json_encode( $data ),
			'headers'    => array(
				'Content-Type' => 'application/json',
			),
		);

		$response = wp_remote_post( self::ENDPOINT, $args );

		// Handle connection errors.
		if ( is_wp_error( $response ) ) {
			return new \WP_Error(
				'registration_connection_failed',
				sprintf(
					/* translators: %s: error message */
					__( 'Could not connect to registration server: %s', 'plugin-wp-support-thisismyurl' ),
					$response->get_error_message()
				)
			);
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$json = json_decode( (string) $body, true );

		// Handle HTTP errors.
		if ( $code < 200 || $code >= 300 ) {
			$error_message = is_array( $json ) && isset( $json['message'] )
				? $json['message']
				: sprintf(
					/* translators: %d: HTTP status code */
					__( 'Registration failed with status code %d', 'plugin-wp-support-thisismyurl' ),
					$code
				);

			return new \WP_Error(
				'registration_http_error',
				$error_message
			);
		}

		// Handle JSON parsing errors.
		if ( ! is_array( $json ) ) {
			return new \WP_Error(
				'registration_invalid_response',
				__( 'Received invalid response from registration server.', 'plugin-wp-support-thisismyurl' )
			);
		}

		// Check if registration was successful.
		$success = isset( $json['success'] ) ? (bool) $json['success'] : false;
		if ( ! $success ) {
			$error_message = isset( $json['message'] )
				? $json['message']
				: __( 'Registration failed. Please try again.', 'plugin-wp-support-thisismyurl' );

			return new \WP_Error(
				'registration_failed',
				$error_message
			);
		}

		return $json;
	}
}
