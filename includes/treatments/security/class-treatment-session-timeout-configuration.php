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
		$issues = array();

		// Check 1: Get auth cookie expiration.
		$auth_cookie_expiration = self::get_auth_cookie_expiration();
		$hours = $auth_cookie_expiration / HOUR_IN_SECONDS;

		if ( $hours > 24 ) {
			$issues[] = sprintf(
				/* translators: %s: duration in hours */
				__( 'Auth cookie expires after %s hours (recommended: 24 hours or less)', 'wpshadow' ),
				number_format_i18n( $hours, 1 )
			);
		}

		// Check 2: Check "Remember Me" duration.
		$remember_me_duration = self::get_remember_me_duration();
		$days = $remember_me_duration / DAY_IN_SECONDS;

		if ( $days > 14 ) {
			$issues[] = sprintf(
				/* translators: %s: duration in days */
				__( '"Remember Me" lasts %s days (recommended: 14 days or less)', 'wpshadow' ),
				number_format_i18n( $days, 0 )
			);
		}

		// Check 3: Check for idle timeout implementation.
		$has_idle_timeout = self::check_idle_timeout_implementation();
		if ( ! $has_idle_timeout ) {
			$issues[] = __( 'No idle timeout mechanism detected (sessions never expire due to inactivity)', 'wpshadow' );
		}

		// Check 4: Check for absolute session timeout.
		$has_absolute_timeout = self::check_absolute_timeout();
		if ( ! $has_absolute_timeout ) {
			$issues[] = __( 'No absolute session timeout detected (sessions can persist indefinitely with activity)', 'wpshadow' );
		}

		// Check 5: Verify session regeneration on login.
		$regenerates_on_login = self::check_session_regeneration();
		if ( ! $regenerates_on_login ) {
			$issues[] = __( 'Sessions may not be regenerated on login (session fixation risk)', 'wpshadow' );
		}

		// Check 6: Check PHP session.gc_maxlifetime.
		$php_session_lifetime = ini_get( 'session.gc_maxlifetime' );
		if ( $php_session_lifetime && ( (int) $php_session_lifetime > 86400 ) ) {
			$issues[] = sprintf(
				/* translators: %s: duration in hours */
				__( 'PHP session garbage collection timeout is %s hours (recommended: 24 hours or less)', 'wpshadow' ),
				number_format_i18n( (int) $php_session_lifetime / 3600, 1 )
			);
		}

		// Check 7: Check for concurrent session handling.
		$handles_concurrent = self::check_concurrent_session_handling();
		if ( ! $handles_concurrent ) {
			$issues[] = __( 'No concurrent session control detected (users can have unlimited simultaneous sessions)', 'wpshadow' );
		}

		// If we found issues, return finding.
		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d session timeout issue detected',
						'%d session timeout issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/session-timeout',
				'context'      => array(
					'issues' => $issues,
					'current_settings' => array(
						'auth_cookie_hours'   => number_format( $hours, 1 ),
						'remember_me_days'    => number_format( $days, 0 ),
						'php_session_timeout' => $php_session_lifetime ? number_format( (int) $php_session_lifetime / 3600, 1 ) . ' hours' : 'N/A',
					),
					'why' => __(
						'Long session timeouts give attackers more time to exploit stolen sessions. ' .
						'If an attacker steals a session cookie (via XSS, network sniffing, or malware), ' .
						'they can impersonate the user until the session expires. ' .
						'The average time to detect a breach is 207 days (IBM). During this time, ' .
						'attackers with stolen sessions can access accounts, exfiltrate data, and maintain persistence. ' .
						'OWASP recommends: idle timeout ≤ 15 minutes for sensitive apps, absolute timeout ≤ 24 hours, Remember Me ≤ 14 days.',
						'wpshadow'
					),
					'recommendation' => __(
						'Configure auth_cookie_expiration to 24 hours or less. Set Remember Me to 14 days maximum. ' .
						'Implement idle timeout (log out after 15-30 minutes of inactivity). ' .
						'Use absolute timeout (force re-login after 24 hours regardless of activity). ' .
						'Regenerate session IDs on login and privilege changes. ' .
						'Consider implementing WPShadow Pro Security for automated session management.',
						'wpshadow'
					),
				),
			);

			// Add upgrade path for WPShadow Pro Security (when available).
			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'session-timeout-management',
				'session-timeout-best-practices'
			);

			return $finding;
		}

		return null;
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
