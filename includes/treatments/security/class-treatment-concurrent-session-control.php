<?php
/**
 * Concurrent Session Control Treatment
 *
 * Detects lack of concurrent session management that could allow
 * unauthorized shared access or session hijacking persistence.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2033.2106
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Concurrent Session Control Treatment Class
 *
 * Checks for:
 * - Unlimited simultaneous sessions per user
 * - No session invalidation on password change
 * - Missing session token management
 * - No device/location tracking for sessions
 * - Inability to revoke specific sessions
 *
 * According to NIST guidelines, systems should limit concurrent
 * sessions and provide mechanisms to terminate active sessions,
 * especially after credential changes or suspicious activity.
 *
 * @since 1.2033.2106
 */
class Treatment_Concurrent_Session_Control extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $slug = 'concurrent-session-control';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $title = 'Concurrent Session Control';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $description = 'Verifies proper concurrent session management and controls';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.2033.2106
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Analyzes concurrent session controls:
	 * 1. Session token management (WP_Session_Tokens)
	 * 2. Session invalidation on password change
	 * 3. Concurrent session limits
	 * 4. Session metadata tracking
	 *
	 * @since  1.2033.2106
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Concurrent_Session_Control' );
	}

	/**
	 * Check for session limit implementation.
	 *
	 * @since  1.2033.2106
	 * @return bool True if limits exist.
	 */
	private static function check_session_limit_implementation() {
		// Check for hooks that limit sessions.
		return has_filter( 'attach_session_information' ) || 
		       has_filter( 'session_token_manager' );
	}

	/**
	 * Check if sessions invalidate on password change.
	 *
	 * @since  1.2033.2106
	 * @return bool True if invalidation occurs.
	 */
	private static function check_password_change_invalidation() {
		// WordPress 4.0+ automatically destroys sessions on password reset.
		// Check if this is being disabled.
		return ! has_filter( 'send_password_change_email', '__return_false' );
	}

	/**
	 * Check for session revocation capability.
	 *
	 * @since  1.2033.2106
	 * @return bool True if revocation exists.
	 */
	private static function check_session_revocation() {
		// WordPress has built-in session destruction.
		return function_exists( 'wp_destroy_other_sessions' ) && 
		       function_exists( 'wp_destroy_all_sessions' );
	}

	/**
	 * Find users with excessive active sessions.
	 *
	 * @since  1.2033.2106
	 * @return array User IDs with excessive sessions.
	 */
	private static function find_users_with_excessive_sessions() {
		global $wpdb;

		$excessive = array();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$users_with_sessions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, meta_value FROM {$wpdb->usermeta} 
				WHERE meta_key = %s 
				LIMIT 100",
				'session_tokens'
			),
			ARRAY_A
		);

		foreach ( $users_with_sessions as $row ) {
			$sessions = maybe_unserialize( $row['meta_value'] );
			if ( is_array( $sessions ) && count( $sessions ) > 10 ) {
				$excessive[] = (int) $row['user_id'];
			}
		}

		return $excessive;
	}
}
