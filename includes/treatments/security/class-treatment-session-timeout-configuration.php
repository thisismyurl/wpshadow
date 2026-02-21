<?php
/**
 * Session Timeout Configuration Treatment
 *
 * Detects insecure session timeout settings that could allow
 * session hijacking or unauthorized persistent access.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.2033.2104
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session Timeout Configuration Treatment Class
 *
 * Checks for:
 * - Session timeout longer than 24 hours
 * - Idle timeout not configured
 * - "Remember Me" duration longer than 14 days
 * - No absolute session timeout
 * - Session timeout not enforced for sensitive operations
 *
 * According to OWASP, improper session timeout is one of the
 * top 10 authentication vulnerabilities. The average time to
 * detect a breach is 207 days (IBM), during which stolen
 * sessions can remain active.
 *
 * @since 1.2033.2104
 */
class Treatment_Session_Timeout_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.2033.2104
	 * @var   string
	 */
	protected static $slug = 'session-timeout-configuration';

	/**
	 * The treatment title
	 *
	 * @since 1.2033.2104
	 * @var   string
	 */
	protected static $title = 'Session Timeout Configuration';

	/**
	 * The treatment description
	 *
	 * @since 1.2033.2104
	 * @var   string
	 */
	protected static $description = 'Verifies session timeout settings follow security best practices';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.2033.2104
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Analyzes session timeout configuration:
	 * 1. Auth cookie expiration time
	 * 2. Remember Me duration
	 * 3. Idle timeout implementation
	 * 4. Session regeneration after privilege escalation
	 *
	 * @since  1.2033.2104
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Session_Timeout_Configuration' );
	}

	/**
	 * Get auth cookie expiration time.
	 *
	 * @since  1.2033.2104
	 * @return int Expiration time in seconds.
	 */
	private static function get_auth_cookie_expiration() {
		// WordPress default is 2 days (172800 seconds).
		$expiration = apply_filters( 'auth_cookie_expiration', 2 * DAY_IN_SECONDS, 0, false );
		return (int) $expiration;
	}

	/**
	 * Get "Remember Me" duration.
	 *
	 * @since  1.2033.2104
	 * @return int Duration in seconds.
	 */
	private static function get_remember_me_duration() {
		// WordPress default is 14 days.
		$duration = apply_filters( 'auth_cookie_expiration', 14 * DAY_IN_SECONDS, 0, true );
		return (int) $duration;
	}

	/**
	 * Check for idle timeout implementation.
	 *
	 * @since  1.2033.2104
	 * @return bool True if implemented.
	 */
	private static function check_idle_timeout_implementation() {
		// Check if any plugin hooks into session validation for idle timeout.
		return has_filter( 'auth_cookie_valid' ) || has_filter( 'determine_current_user' );
	}

	/**
	 * Check for absolute session timeout.
	 *
	 * @since  1.2033.2104
	 * @return bool True if implemented.
	 */
	private static function check_absolute_timeout() {
		// Check for custom session timeout implementation.
		global $wpdb;

		// Look for session metadata tracking creation time.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$has_timeout_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE '%session_start%' 
			OR meta_key LIKE '%login_time%'"
		);

		return $has_timeout_meta > 0;
	}

	/**
	 * Check if sessions regenerate on login.
	 *
	 * @since  1.2033.2104
	 * @return bool True if regeneration occurs.
	 */
	private static function check_session_regeneration() {
		// WordPress regenerates session on login by default.
		// Check if any plugin is disabling it.
		return ! has_filter( 'send_auth_cookies', '__return_false' );
	}

	/**
	 * Check for concurrent session handling.
	 *
	 * @since  1.2033.2104
	 * @return bool True if handled.
	 */
	private static function check_concurrent_session_handling() {
		// Check for session tokens table or metadata.
		global $wpdb;

		// WordPress 4.0+ uses session tokens.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$has_session_tokens = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} 
			WHERE meta_key = 'session_tokens'"
		);

		return $has_session_tokens > 0;
	}
}
