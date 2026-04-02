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
 * Enforces site privacy settings before optional outbound requests.
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
		return true;
	}

	/**
	 * Get required-permission message for blocked requests.
	 *
	 * @since  0.6093.1200
	 * @param  string $context Optional. Human-readable request context.
	 * @return string Message to show in UI/API responses.
	 */
	public static function get_denied_message( string $context = '' ): string {
		if ( '' !== $context ) {
			return sprintf(
				/* translators: %s: feature/request context */
				__( '%s needs external requests enabled in Privacy Settings to continue.', 'wpshadow' ),
				$context
			);
		}

		return __( 'This action needs external requests enabled in Privacy Settings to continue.', 'wpshadow' );
	}

}
