<?php
/**
 * Input Sanitization Helpers
 *
 * Centralized input sanitization functions to eliminate duplicate sanitization
 * patterns across the plugin.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73003
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get sanitized text field from POST data.
 *
 * @param string $key     POST array key.
 * @param string $default Default value if key not set.
 * @return string Sanitized text value.
 */
function wpshadow_get_post_text( string $key, string $default = '' ): string {
	return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $default;
}

/**
 * Get sanitized email from POST data.
 *
 * @param string $key     POST array key.
 * @param string $default Default value if key not set.
 * @return string Sanitized email value.
 */
function wpshadow_get_post_email( string $key, string $default = '' ): string {
	return isset( $_POST[ $key ] ) ? sanitize_email( wp_unslash( $_POST[ $key ] ) ) : $default;
}

/**
 * Get sanitized URL from POST data.
 *
 * @param string $key     POST array key.
 * @param string $default Default value if key not set.
 * @return string Sanitized URL value.
 */
function wpshadow_get_post_url( string $key, string $default = '' ): string {
	return isset( $_POST[ $key ] ) ? esc_url_raw( wp_unslash( $_POST[ $key ] ) ) : $default;
}

/**
 * Get sanitized integer from POST data.
 *
 * @param string $key     POST array key.
 * @param int    $default Default value if key not set.
 * @return int Sanitized integer value.
 */
function wpshadow_get_post_int( string $key, int $default = 0 ): int {
	return isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : $default;
}

/**
 * Get sanitized key from POST data.
 *
 * @param string $key     POST array key.
 * @param string $default Default value if key not set.
 * @return string Sanitized key value.
 */
function wpshadow_get_post_key( string $key, string $default = '' ): string {
	return isset( $_POST[ $key ] ) ? sanitize_key( wp_unslash( $_POST[ $key ] ) ) : $default;
}

/**
 * Get boolean value from POST data.
 *
 * @param string $key     POST array key.
 * @param bool   $default Default value if key not set.
 * @return bool Boolean value.
 */
function wpshadow_get_post_bool( string $key, bool $default = false ): bool {
	return isset( $_POST[ $key ] ) ? (bool) $_POST[ $key ] : $default;
}

/**
 * Get sanitized textarea content from POST data.
 *
 * @param string $key     POST array key.
 * @param string $default Default value if key not set.
 * @return string Sanitized textarea content.
 */
function wpshadow_get_post_textarea( string $key, string $default = '' ): string {
	return isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : $default;
}

/**
 * Get sanitized text field from GET data.
 *
 * @param string $key     GET array key.
 * @param string $default Default value if key not set.
 * @return string Sanitized text value.
 */
function wpshadow_get_query_text( string $key, string $default = '' ): string {
	return isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : $default;
}

/**
 * Get sanitized key from GET data.
 *
 * @param string $key     GET array key.
 * @param string $default Default value if key not set.
 * @return string Sanitized key value.
 */
function wpshadow_get_query_key( string $key, string $default = '' ): string {
	return isset( $_GET[ $key ] ) ? sanitize_key( wp_unslash( $_GET[ $key ] ) ) : $default;
}

/**
 * Get sanitized integer from GET data.
 *
 * @param string $key     GET array key.
 * @param int    $default Default value if key not set.
 * @return int Sanitized integer value.
 */
function wpshadow_get_query_int( string $key, int $default = 0 ): int {
	return isset( $_GET[ $key ] ) ? absint( $_GET[ $key ] ) : $default;
}

/**
 * Get sanitized URL from GET data.
 *
 * @param string $key     GET array key.
 * @param string $default Default value if key not set.
 * @return string Sanitized URL value.
 */
function wpshadow_get_query_url( string $key, string $default = '' ): string {
	return isset( $_GET[ $key ] ) ? esc_url_raw( wp_unslash( $_GET[ $key ] ) ) : $default;
}

/**
 * Get boolean value from GET data.
 *
 * @param string $key     GET array key.
 * @param bool   $default Default value if key not set.
 * @return bool Boolean value.
 */
function wpshadow_get_query_bool( string $key, bool $default = false ): bool {
	return isset( $_GET[ $key ] ) ? (bool) $_GET[ $key ] : $default;
}

/**
 * Get array of sanitized text fields from POST data.
 *
 * @param string $key     POST array key.
 * @param array  $default Default value if key not set.
 * @return array Array of sanitized text values.
 */
function wpshadow_get_post_array( string $key, array $default = array() ): array {
	if ( ! isset( $_POST[ $key ] ) || ! is_array( $_POST[ $key ] ) ) {
		return $default;
	}

	return array_map(
		function ( $value ) {
			return is_string( $value ) ? sanitize_text_field( wp_unslash( $value ) ) : $value;
		},
		$_POST[ $key ]
	);
}

/**
 * Get array of sanitized keys from POST data.
 *
 * @param string $key     POST array key.
 * @param array  $default Default value if key not set.
 * @return array Array of sanitized key values.
 */
function wpshadow_get_post_key_array( string $key, array $default = array() ): array {
	if ( ! isset( $_POST[ $key ] ) || ! is_array( $_POST[ $key ] ) ) {
		return $default;
	}

	return array_map( 'sanitize_key', array_map( 'wp_unslash', $_POST[ $key ] ) );
}
