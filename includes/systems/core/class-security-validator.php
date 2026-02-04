<?php
/**
 * Security Validator Utility
 *
 * Centralized security validation methods to reduce code duplication.
 * Consolidates nonce verification, capability checks, and permission errors.
 *
 * @package    WPShadow
 * @subpackage Core
 * @since      1.6031.1500
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Validator Class
 *
 * Provides reusable security validation methods for non-AJAX contexts.
 * For AJAX handlers, use AJAX_Handler_Base instead.
 *
 * @since 1.6031.1500
 */
class Security_Validator {

	/**
	 * Verify user has required capability.
	 *
	 * Multisite-aware capability checking with standardized error handling.
	 *
	 * @since  1.6031.1500
	 * @param  string $capability Required capability. Default 'manage_options'.
	 * @param  bool   $die        Whether to wp_die on failure. Default true.
	 * @return bool True if user has capability, false otherwise.
	 */
	public static function verify_capability( string $capability = 'manage_options', bool $die = true ): bool {
		// Check multisite network admin capability
		if ( is_multisite() && is_network_admin() ) {
			$capability = 'manage_network' === $capability ? 'manage_network_options' : $capability;
		}

		$has_capability = current_user_can( $capability );

		if ( ! $has_capability && $die ) {
			wp_die(
				self::get_permission_error( $capability ),
				esc_html__( 'Permission Denied', 'wpshadow' ),
				array( 'response' => 403 )
			);
		}

		return $has_capability;
	}

	/**
	 * Verify nonce for form submissions.
	 *
	 * @since  1.6031.1500
	 * @param  string $action      Nonce action.
	 * @param  string $nonce_field Nonce field name. Default '_wpnonce'.
	 * @param  bool   $die         Whether to wp_die on failure. Default true.
	 * @return bool True if nonce is valid, false otherwise.
	 */
	public static function verify_nonce( string $action, string $nonce_field = '_wpnonce', bool $die = true ): bool {
		$nonce = isset( $_POST[ $nonce_field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $nonce_field ] ) ) : '';

		$valid = wp_verify_nonce( $nonce, $action );

		if ( ! $valid && $die ) {
			wp_die(
				esc_html__( 'Security check failed. Please try again.', 'wpshadow' ),
				esc_html__( 'Security Error', 'wpshadow' ),
				array( 'response' => 403 )
			);
		}

		return (bool) $valid;
	}

	/**
	 * Verify nonce and capability together.
	 *
	 * Common pattern for form submissions that require both checks.
	 *
	 * @since  1.6031.1500
	 * @param  string $action      Nonce action.
	 * @param  string $capability  Required capability. Default 'manage_options'.
	 * @param  string $nonce_field Nonce field name. Default '_wpnonce'.
	 * @return bool True if both checks pass.
	 */
	public static function verify_request( string $action, string $capability = 'manage_options', string $nonce_field = '_wpnonce' ): bool {
		self::verify_nonce( $action, $nonce_field, true );
		self::verify_capability( $capability, true );
		return true;
	}

	/**
	 * Get standardized permission error message.
	 *
	 * @since  1.6031.1500
	 * @param  string $capability Required capability that was missing.
	 * @return string Localized error message.
	 */
	public static function get_permission_error( string $capability = 'manage_options' ): string {
		$messages = array(
			'manage_options'         => __( 'You do not have permission to manage settings for this site.', 'wpshadow' ),
			'manage_network_options' => __( 'You do not have permission to manage network settings.', 'wpshadow' ),
			'edit_posts'             => __( 'You do not have permission to edit posts.', 'wpshadow' ),
			'edit_pages'             => __( 'You do not have permission to edit pages.', 'wpshadow' ),
			'edit_users'             => __( 'You do not have permission to edit users.', 'wpshadow' ),
			'delete_users'           => __( 'You do not have permission to delete users.', 'wpshadow' ),
		);

		return isset( $messages[ $capability ] )
			? $messages[ $capability ]
			: sprintf(
				/* translators: %s: capability name */
				__( 'You do not have the required permission: %s', 'wpshadow' ),
				$capability
			);
	}

	/**
	 * Check if current request is from admin area.
	 *
	 * @since  1.6031.1500
	 * @return bool True if in admin area.
	 */
	public static function is_admin_request(): bool {
		return is_admin() && ! wp_doing_ajax();
	}

	/**
	 * Check if current request is AJAX.
	 *
	 * @since  1.6031.1500
	 * @return bool True if AJAX request.
	 */
	public static function is_ajax_request(): bool {
		return wp_doing_ajax();
	}

	/**
	 * Sanitize and validate email address.
	 *
	 * @since  1.6031.1500
	 * @param  string $email Email to validate.
	 * @return string|false Sanitized email or false if invalid.
	 */
	public static function sanitize_email( string $email ) {
		$sanitized = sanitize_email( $email );
		return is_email( $sanitized ) ? $sanitized : false;
	}

	/**
	 * Sanitize and validate URL.
	 *
	 * @since  1.6031.1500
	 * @param  string $url URL to validate.
	 * @return string|false Sanitized URL or false if invalid.
	 */
	public static function sanitize_url( string $url ) {
		$sanitized = esc_url_raw( $url );
		return filter_var( $sanitized, FILTER_VALIDATE_URL ) ? $sanitized : false;
	}

	/**
	 * Validate file path for security
	 *
	 * Ensures file path is within allowed directory to prevent path traversal attacks.
	 * Resolves symlinks and checks against base directory.
	 *
	 * @since  1.6032.1000
	 * @param  string $file_path File path to validate.
	 * @param  string $base_dir  Optional base directory (default: WPSHADOW_PATH).
	 * @return string|false Validated real path or false if invalid.
	 */
	public static function validate_file_path( string $file_path, string $base_dir = '' ): ?string {
		// Sanitize input
		if ( empty( $file_path ) ) {
			return null;
		}

		// Use default base if not provided
		if ( empty( $base_dir ) ) {
			$base_dir = defined( 'WPSHADOW_PATH' ) ? WPSHADOW_PATH : WP_PLUGIN_DIR;
		}

		// Resolve symlinks and relative paths
		$real_path = realpath( $file_path );
		$real_base = realpath( $base_dir );

		// Both must be valid and resolvable
		if ( ! $real_path || ! $real_base ) {
			return null;
		}

		// Ensure path is within base directory (prevents ../../../ traversal)
		if ( strpos( $real_path, $real_base ) !== 0 ) {
			return null;
		}

		return $real_path;
	}

	/**
	 * Validate file path with multiple allowed directories
	 *
	 * @since  1.6032.1000
	 * @param  string $file_path   File path to validate.
	 * @param  array  $allowed_dirs Array of allowed base directories.
	 * @return string|false Validated real path or false if invalid.
	 */
	public static function validate_file_path_multi( string $file_path, array $allowed_dirs ): ?string {
		if ( empty( $file_path ) || empty( $allowed_dirs ) ) {
			return null;
		}

		$real_path = realpath( $file_path );
		if ( ! $real_path ) {
			return null;
		}

		foreach ( $allowed_dirs as $base_dir ) {
			$real_base = realpath( $base_dir );
			if ( ! $real_base ) {
				continue;
			}

			// Check if path is within this directory
			if ( strpos( $real_path, $real_base ) === 0 ) {
				return $real_path;
			}
		}

		return null;
	}
}
