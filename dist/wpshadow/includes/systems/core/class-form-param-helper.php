<?php
/**
 * Form Parameter Helper
 *
 * Centralized helper for extracting, validating, and sanitizing form parameters
 * from $_POST, $_GET, and $_REQUEST with consistent patterns across the plugin.
 *
 * Reduces code duplication by providing single-line parameter extraction with
 * automatic sanitization, validation, and sensible defaults.
 *
 * @package WPShadow\Core
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form_Param_Helper Class
 *
 * Provides static methods for safe form parameter extraction with built-in sanitization.
 *
 * Usage Examples:
 *   // Get text from POST with default
 *   $name = Form_Param_Helper::post( 'name', 'text', 'Guest' );
 *
 *   // Get required email from POST (fails if missing)
 *   $email = Form_Param_Helper::post_required( 'email', 'email' );
 *
 *   // Get integer from GET
 *   $page = Form_Param_Helper::get( 'page', 'int', 1 );
 *
 *   // Get multiple params at once
 *   $data = Form_Param_Helper::post_multiple( array(
 *       'name'  => 'text',
 *       'email' => 'email',
 *       'count' => 'int',
 *   ) );
 *
 * @since 0.6093.1200
 */
class Form_Param_Helper {

	/**
	 * Supported sanitization types
	 *
	 * @var array
	 */
	private static $supported_types = array(
		'text',       // sanitize_text_field
		'email',      // sanitize_email
		'key',        // sanitize_key
		'textarea',   // sanitize_textarea_field
		'int',        // intval
		'bool',       // rest_sanitize_boolean
		'url',        // esc_url_raw
	);

	/**
	 * Get parameter from $_POST with sanitization
	 *
	 * @since 0.6093.1200
	 * @param  string $key          Parameter key.
	 * @param  string $type         Sanitization type (text, email, key, textarea, int, bool, url).
	 * @param  mixed  $default      Default value if not found.
	 * @return mixed Sanitized value or default.
	 */
	public static function post( string $key, string $type = 'text', $default = '' ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			return $default;
		}

		return self::sanitize( wp_unslash( $_POST[ $key ] ), $type );
	}

	/**
	 * Get required parameter from $_POST
	 *
	 * Returns default if missing, but you should check for it and handle errors yourself.
	 * Best used with proper error handling in your calling code.
	 *
	 * @since 0.6093.1200
	 * @param  string $key  Parameter key.
	 * @param  string $type Sanitization type.
	 * @return mixed Sanitized value.
	 */
	public static function post_required( string $key, string $type = 'text' ) {
		$value = self::post( $key, $type, null );

		if ( null === $value ) {
			return '';
		}

		return $value;
	}

	/**
	 * Get parameter from $_GET with sanitization
	 *
	 * @since 0.6093.1200
	 * @param  string $key          Parameter key.
	 * @param  string $type         Sanitization type (text, email, key, textarea, int, bool, url).
	 * @param  mixed  $default      Default value if not found.
	 * @return mixed Sanitized value or default.
	 */
	public static function get( string $key, string $type = 'text', $default = '' ) {
		if ( ! isset( $_GET[ $key ] ) ) {
			return $default;
		}

		return self::sanitize( wp_unslash( $_GET[ $key ] ), $type );
	}

	/**
	 * Get parameter from $_REQUEST (POST or GET)
	 *
	 * Tries $_POST first, then $_GET. Useful for forms that support both.
	 *
	 * @since 0.6093.1200
	 * @param  string $key          Parameter key.
	 * @param  string $type         Sanitization type.
	 * @param  mixed  $default      Default value if not found.
	 * @return mixed Sanitized value or default.
	 */
	public static function request( string $key, string $type = 'text', $default = '' ) {
		if ( isset( $_POST[ $key ] ) ) {
			return self::post( $key, $type, $default );
		}

		if ( isset( $_GET[ $key ] ) ) {
			return self::get( $key, $type, $default );
		}

		return $default;
	}

	/**
	 * Get multiple parameters from $_POST at once
	 *
	 * @since 0.6093.1200
	 * @param  array $params Associative array of key => type pairs.
	 *                       Example: array( 'name' => 'text', 'email' => 'email', 'count' => 'int' )
	 * @param  array $defaults Optional. Default values per key. Example: array( 'name' => 'Guest' )
	 * @return array Associative array of sanitized values.
	 */
	public static function post_multiple( array $params, array $defaults = array() ): array {
		$result = array();

		foreach ( $params as $key => $type ) {
			$default = $defaults[ $key ] ?? '';
			$result[ $key ] = self::post( $key, $type, $default );
		}

		return $result;
	}

	/**
	 * Get multiple parameters from $_GET at once
	 *
	 * @since 0.6093.1200
	 * @param  array $params Associative array of key => type pairs.
	 * @param  array $defaults Optional. Default values per key.
	 * @return array Associative array of sanitized values.
	 */
	public static function get_multiple( array $params, array $defaults = array() ): array {
		$result = array();

		foreach ( $params as $key => $type ) {
			$default = $defaults[ $key ] ?? '';
			$result[ $key ] = self::get( $key, $type, $default );
		}

		return $result;
	}

	/**
	 * Sanitize a value based on type
	 *
	 * Internal method used by all public methods.
	 *
	 * @since 0.6093.1200
	 * @param  mixed  $value Value to sanitize.
	 * @param  string $type  Sanitization type.
	 * @return mixed Sanitized value.
	 */
	private static function sanitize( $value, string $type ) {
		// Handle empty values
		if ( empty( $value ) && '0' !== (string) $value ) {
			// Return sensible defaults for various types
			switch ( $type ) {
				case 'int':
					return 0;
				case 'bool':
					return false;
				default:
					return '';
			}
		}

		// Sanitize based on type
		switch ( $type ) {
			case 'text':
				return sanitize_text_field( $value );

			case 'email':
				return sanitize_email( $value );

			case 'key':
				return sanitize_key( $value );

			case 'textarea':
				return sanitize_textarea_field( $value );

			case 'int':
				return absint( $value );

			case 'bool':
				return rest_sanitize_boolean( $value );

			case 'url':
				return esc_url_raw( $value );

			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Check if parameter exists in $_POST
	 *
	 * @since 0.6093.1200
	 * @param  string $key Parameter key.
	 * @return bool True if parameter exists, false otherwise.
	 */
	public static function has_post( string $key ): bool {
		return isset( $_POST[ $key ] );
	}

	/**
	 * Check if parameter exists in $_GET
	 *
	 * @since 0.6093.1200
	 * @param  string $key Parameter key.
	 * @return bool True if parameter exists, false otherwise.
	 */
	public static function has_get( string $key ): bool {
		return isset( $_GET[ $key ] );
	}

	/**
	 * Check if parameter exists in $_REQUEST
	 *
	 * @since 0.6093.1200
	 * @param  string $key Parameter key.
	 * @return bool True if parameter exists, false otherwise.
	 */
	public static function has_request( string $key ): bool {
		return isset( $_POST[ $key ] ) || isset( $_GET[ $key ] );
	}
}
