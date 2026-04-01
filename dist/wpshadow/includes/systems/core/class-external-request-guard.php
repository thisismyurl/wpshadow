<?php
/**
 * External Request Guard
 *
 * Centralized permission checks for outbound HTTP requests.
 *
 * @package    WPShadow
 * @subpackage Core
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * External_Request_Guard Class
 *
 * Enforces user/site consent before optional outbound requests.
 *
 * @since 0.6093.1200
 */
class External_Request_Guard {

	/**
	 * Check if an outbound request is allowed.
	 *
	 * @since  0.6093.1200
	 * @param  string   $purpose Optional. Purpose key for the request.
	 * @param  int|null $user_id Optional. User context. Defaults to current user.
	 * @return bool True when request is allowed.
	 */
	public static function is_allowed( string $purpose = 'general', ?int $user_id = null ): bool {
		if ( defined( 'WPSHADOW_ALLOW_EXTERNAL_REQUESTS' ) && WPSHADOW_ALLOW_EXTERNAL_REQUESTS ) {
			return true;
		}

		$purpose = sanitize_key( $purpose );
		if ( '' === $purpose ) {
			$purpose = 'general';
		}

		$globally_enabled = (bool) get_option( 'wpshadow_privacy_telemetry_enabled', false );
		if ( $globally_enabled ) {
			return true;
		}

		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( $user_id > 0 && class_exists( '\\WPShadow\\Privacy\\Consent_Preferences' ) ) {
			$prefs = \WPShadow\Privacy\Consent_Preferences::get_preferences( $user_id );
			if ( ! empty( $prefs['anonymized_telemetry'] ) ) {
				return true;
			}
		}

		self::log_blocked_request( $purpose, $user_id );
		return false;
	}

	/**
	 * Get consent-required message for blocked requests.
	 *
	 * @since  0.6093.1200
	 * @param  string $context Optional. Human-readable request context.
	 * @return string Message to show in UI/API responses.
	 */
	public static function get_denied_message( string $context = '' ): string {
		if ( '' !== $context ) {
			return sprintf(
				/* translators: %s: feature/request context */
				__( '%s needs your permission before contacting external services. Enable Anonymous Analytics in Privacy Settings to continue.', 'wpshadow' ),
				$context
			);
		}

		return __( 'This action needs your permission before contacting external services. Enable Anonymous Analytics in Privacy Settings to continue.', 'wpshadow' );
	}

	/**
	 * Log blocked outbound requests for transparency.
	 *
	 * @since  0.6093.1200
	 * @param  string $purpose Request purpose key.
	 * @param  int    $user_id User identifier.
	 * @return void
	 */
	private static function log_blocked_request( string $purpose, int $user_id ): void {
		if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
			Activity_Logger::log(
				'external_request_blocked',
				array(
					'purpose' => $purpose,
					'user_id' => $user_id,
				)
			);
		}
	}
}
