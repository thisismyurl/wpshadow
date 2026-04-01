<?php
/**
 * Security Hardening Utilities
 *
 * Additional security measures and validation functions for WPShadow.
 * Implements defense-in-depth security practices.
 *
 * @package    WPShadow
 * @subpackage Core
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security_Hardening Class
 *
 * Provides additional security validation and hardening utilities.
 *
 * @since 0.6093.1200
 */
class Security_Hardening {

	/**
	 * Validate table name is safe for SQL operations
	 *
	 * Ensures table names contain only alphanumeric characters and underscores.
	 * Prevents SQL injection through table name manipulation.
	 *
	 * @since 0.6093.1200
	 * @param  string $table_name Table name to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public static function is_valid_table_name( string $table_name ): bool {
		// Only allow alphanumeric and underscore characters.
		return (bool) preg_match( '/^[a-zA-Z0-9_]+$/', $table_name );
	}

	/**
	 * Validate file path is within allowed directory
	 *
	 * Prevents directory traversal attacks by ensuring paths stay within
	 * allowed boundaries.
	 *
	 * @since 0.6093.1200
	 * @param  string $path Path to validate.
	 * @param  string $allowed_base Allowed base directory.
	 * @return bool True if safe, false if outside allowed directory.
	 */
	public static function is_path_within_directory( string $path, string $allowed_base ): bool {
		$normalized_path = wp_normalize_path( realpath( $path ) ?: $path );
		$normalized_base = wp_normalize_path( realpath( $allowed_base ) ?: $allowed_base );

		// Ensure path starts with allowed base (is within directory).
		return 0 === strpos( $normalized_path, $normalized_base );
	}

	/**
	 * Sanitize and validate workflow ID
	 *
	 * @since 0.6093.1200
	 * @param  string $workflow_id Workflow ID to validate.
	 * @return string|false Sanitized ID or false if invalid.
	 */
	public static function sanitize_workflow_id( string $workflow_id ) {
		// Workflow IDs should be alphanumeric with hyphens and underscores.
		if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $workflow_id ) ) {
			return false;
		}

		return sanitize_key( $workflow_id );
	}

	/**
	 * Hash sensitive token for secure storage
	 *
	 * Use this for magic links, API tokens, etc.
	 * Store the hash, compare on validation (like password hashing).
	 *
	 * @since 0.6093.1200
	 * @param  string $token Token to hash.
	 * @return string Hashed token.
	 */
	public static function hash_token( string $token ): string {
		return hash_hmac( 'sha256', $token, wp_salt( 'auth' ) );
	}

	/**
	 * Verify hashed token
	 *
	 * @since 0.6093.1200
	 * @param  string $token Token to verify.
	 * @param  string $hash Stored hash.
	 * @return bool True if matches, false otherwise.
	 */
	public static function verify_token( string $token, string $hash ): bool {
		return hash_equals( $hash, self::hash_token( $token ) );
	}

	/**
	 * Check if IP address is from GitHub (for webhooks)
	 *
	 * @since 0.6093.1200
	 * @param  string $ip IP address to check.
	 * @return bool True if from GitHub, false otherwise.
	 */
	public static function is_github_ip( string $ip ): bool {
		$github_ips = get_transient( 'wpshadow_github_ips' );

		if ( false === $github_ips ) {
			// Fetch from GitHub API.
			$response = wp_remote_get(
				'https://api.github.com/meta',
				array(
					'timeout' => 10,
					'headers' => array( 'User-Agent' => 'WPShadow-Security' ),
				)
			);

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( ! isset( $body['hooks'] ) ) {
				return false;
			}

			$github_ips = $body['hooks'];
			set_transient( 'wpshadow_github_ips', $github_ips, HOUR_IN_SECONDS );
		}

		// Check if IP is in any of GitHub's CIDR ranges.
		foreach ( $github_ips as $range ) {
			if ( self::ip_in_range( $ip, $range ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if IP is in CIDR range
	 *
	 * @since 0.6093.1200
	 * @param  string $ip IP address.
	 * @param  string $range CIDR range (e.g., 192.30.252.0/22).
	 * @return bool True if in range.
	 */
	private static function ip_in_range( string $ip, string $range ): bool {
		list( $subnet, $mask ) = array_pad( explode( '/', $range ), 2, 32 );

		$ip_long     = ip2long( $ip );
		$subnet_long = ip2long( $subnet );
		$mask_long   = -1 << ( 32 - (int) $mask );

		return ( $ip_long & $mask_long ) === ( $subnet_long & $mask_long );
	}

	/**
	 * Validate email is approved for workflow notifications
	 *
	 * Prevents arbitrary email sending by requiring pre-approval.
	 *
	 * @since 0.6093.1200
	 * @param  string $email Email address to check.
	 * @return bool True if approved, false otherwise.
	 */
	public static function is_approved_email( string $email ): bool {
		$email = sanitize_email( $email );
		if ( empty( $email ) ) {
			return false;
		}

		// Admin email is always approved.
		if ( $email === get_option( 'admin_email' ) ) {
			return true;
		}

		// Check approved list.
		$approved_emails = get_option( 'wpshadow_approved_workflow_emails', array() );
		return in_array( $email, $approved_emails, true );
	}

	/**
	 * Sanitize SQL table name (escape for backtick context)
	 *
	 * @since 0.6093.1200
	 * @param  string $table_name Table name.
	 * @return string Sanitized table name.
	 */
	public static function sanitize_table_name( string $table_name ): string {
		// Remove any backticks first.
		$table_name = str_replace( '`', '', $table_name );

		// Validate format.
		if ( ! self::is_valid_table_name( $table_name ) ) {
			return '';
		}

		return esc_sql( $table_name );
	}

	/**
	 * Check if current request is from a safe source
	 *
	 * @since 0.6093.1200
	 * @return bool True if safe, false if suspicious.
	 */
	public static function is_safe_request(): bool {
		// Check for suspicious user agents.
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		$suspicious_patterns = array(
			'/curl/i',
			'/wget/i',
			'/python/i',
			'/nikto/i',
			'/sqlmap/i',
		);

		foreach ( $suspicious_patterns as $pattern ) {
			if ( preg_match( $pattern, $user_agent ) ) {
				// Log suspicious request.
				Error_Handler::log_warning(
					'Suspicious user agent detected',
					array(
						'user_agent' => $user_agent,
						'ip'         => self::get_client_ip(),
						'url'        => isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '',
					)
				);
				return false;
			}
		}

		return true;
	}

	/**
	 * Get client IP address (handles proxies)
	 *
	 * @since 0.6093.1200
	 * @return string IP address.
	 */
	public static function get_client_ip(): string {
		$headers = array(
			'HTTP_CF_CONNECTING_IP',    // Cloudflare.
			'HTTP_X_FORWARDED_FOR',     // Proxy.
			'HTTP_X_REAL_IP',           // Nginx proxy.
			'REMOTE_ADDR',              // Direct connection.
		);

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
				// Handle comma-separated IPs (X-Forwarded-For).
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = trim( explode( ',', $ip )[0] );
				}
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}

	/**
	 * Rate limit check for sensitive operations
	 *
	 * @since 0.6093.1200
	 * @param  string $action Action identifier.
	 * @param  int    $max_attempts Maximum attempts per period.
	 * @param  int    $period Period in seconds.
	 * @return bool True if within limit, false if exceeded.
	 */
	public static function check_rate_limit( string $action, int $max_attempts = 10, int $period = 60 ): bool {
		$ip          = self::get_client_ip();
		$transient_key = 'wpshadow_rate_' . md5( $action . $ip );
		$attempts    = get_transient( $transient_key );

		if ( false === $attempts ) {
			set_transient( $transient_key, 1, $period );
			return true;
		}

		if ( $attempts >= $max_attempts ) {
			// Log rate limit exceeded.
			Error_Handler::log_warning(
				'Rate limit exceeded',
				array(
					'action'   => $action,
					'ip'       => $ip,
					'attempts' => $attempts,
				)
			);
			return false;
		}

		set_transient( $transient_key, $attempts + 1, $period );
		return true;
	}

	/**
	 * Sanitize dangerous PHP functions from code
	 *
	 * Returns array of dangerous functions found.
	 *
	 * @since 0.6093.1200
	 * @param  string $code PHP code to check.
	 * @return array Array of dangerous functions found.
	 */
	public static function scan_for_dangerous_functions( string $code ): array {
		$dangerous = array(
			'eval',
			'exec',
			'system',
			'shell_exec',
			'passthru',
			'popen',
			'proc_open',
			'pcntl_exec',
			'assert',
			'create_function',
			'include',
			'include_once',
			'require',
			'require_once',
			'file_get_contents',
			'file_put_contents',
			'fopen',
			'readfile',
			'curl_exec',
			'unlink',
			'rmdir',
			'chmod',
		);

		$found = array();
		foreach ( $dangerous as $func ) {
			if ( preg_match( '/\b' . preg_quote( $func, '/' ) . '\s*\(/i', $code ) ) {
				$found[] = $func;
			}
		}

		return $found;
	}

	/**
	 * Add security headers to response
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function add_security_headers(): void {
		if ( headers_sent() ) {
			return;
		}

		// Prevent clickjacking.
		header( 'X-Frame-Options: SAMEORIGIN' );

		// Prevent MIME sniffing.
		header( 'X-Content-Type-Options: nosniff' );

		// XSS protection (older browsers).
		header( 'X-XSS-Protection: 1; mode=block' );

		// Referrer policy.
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );

		// Content Security Policy (basic).
		header( "Content-Security-Policy: frame-ancestors 'self'" );
	}
}
