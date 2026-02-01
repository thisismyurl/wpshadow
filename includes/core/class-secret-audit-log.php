<?php
/**
 * Secret Audit Log
 *
 * Tracks all access to encrypted secrets for security auditing.
 * Records who accessed what secret, when, and from where.
 *
 * @package    WPShadow
 * @subpackage Core
 * @since      1.26032.1000
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Secret Audit Log Class
 *
 * Provides audit trail for secret access and modifications.
 *
 * @since 1.26032.1000
 */
class Secret_Audit_Log {

	/**
	 * Log secret access
	 *
	 * @since  1.26032.1000
	 * @param  string $key_name Secret key name.
	 * @param  string $action   Action performed (created|retrieved|updated|deleted).
	 * @return void
	 */
	public static function log_access( string $key_name, string $action ): void {
		$log_entry = array(
			'key_name'   => $key_name,
			'action'     => $action,
			'user_id'    => get_current_user_id(),
			'user_login' => self::get_user_login(),
			'ip_address' => self::get_client_ip(),
			'timestamp'  => current_time( 'mysql' ),
		);

		// Use Activity Logger for central logging
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			Activity_Logger::log( 'secret_access', $log_entry );
		}
	}

	/**
	 * Get current user login
	 *
	 * @since  1.26032.1000
	 * @return string User login or 'unknown'.
	 */
	private static function get_user_login(): string {
		$user = wp_get_current_user();
		if ( $user instanceof \WP_User && $user->ID > 0 ) {
			return $user->user_login;
		}
		return 'unknown';
	}

	/**
	 * Get client IP address
	 *
	 * Handles various proxy scenarios to get real client IP.
	 *
	 * @since  1.26032.1000
	 * @return string Client IP address.
	 */
	private static function get_client_ip(): string {
		// Check for IPs passed from proxy
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
			if ( self::is_valid_ip( $ip ) ) {
				return $ip;
			}
		}

		// Check for IPs passed from remote proxy
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$ip = trim( $ips[0] );
			if ( self::is_valid_ip( $ip ) ) {
				return $ip;
			}
		}

		// Fall back to REMOTE_ADDR
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
			if ( self::is_valid_ip( $ip ) ) {
				return $ip;
			}
		}

		return '0.0.0.0';
	}

	/**
	 * Validate IP address format
	 *
	 * @since  1.26032.1000
	 * @param  string $ip IP address to validate.
	 * @return bool True if valid IPv4 or IPv6.
	 */
	private static function is_valid_ip( string $ip ): bool {
		return false !== filter_var( $ip, FILTER_VALIDATE_IP );
	}

	/**
	 * Get access logs for a specific secret
	 *
	 * @since  1.26032.1000
	 * @param  string $key_name Secret key name.
	 * @param  int    $limit    Number of records to return. Default 50.
	 * @return array Array of log entries.
	 */
	public static function get_logs_for_key( string $key_name, int $limit = 50 ): array {
		if ( ! class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			return array();
		}

		// This would query the activity log table
		// Placeholder: actual implementation depends on Activity_Logger structure
		return array();
	}

	/**
	 * Check if secret was recently accessed
	 *
	 * @since  1.26032.1000
	 * @param  string $key_name    Secret key name.
	 * @param  int    $minutes_ago Check access within last N minutes. Default 60.
	 * @return bool True if accessed recently.
	 */
	public static function was_accessed_recently( string $key_name, int $minutes_ago = 60 ): bool {
		// This would query the activity log
		// Placeholder: actual implementation depends on Activity_Logger
		return false;
	}

	/**
	 * Check if secret was accessed by user
	 *
	 * @since  1.26032.1000
	 * @param  string $key_name Secret key name.
	 * @param  int    $user_id  User ID to check.
	 * @return bool True if user accessed this secret.
	 */
	public static function was_accessed_by_user( string $key_name, int $user_id ): bool {
		// This would query the activity log
		// Placeholder: actual implementation depends on Activity_Logger
		return false;
	}
}
