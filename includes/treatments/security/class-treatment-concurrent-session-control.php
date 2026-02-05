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
		global $wpdb;

		$issues = array();

		// Check 1: Verify WordPress session token system is active.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$has_session_tokens = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} 
			WHERE meta_key = 'session_tokens'"
		);

		if ( 0 === (int) $has_session_tokens ) {
			$issues[] = __( 'WordPress session token system not in use (no session management)', 'wpshadow' );
		}

		// Check 2: Look for session limit implementation.
		$has_session_limit = self::check_session_limit_implementation();
		if ( ! $has_session_limit ) {
			$issues[] = __( 'No concurrent session limit detected (users can have unlimited simultaneous sessions)', 'wpshadow' );
		}

		// Check 3: Check if sessions are invalidated on password change.
		$invalidates_on_password_change = self::check_password_change_invalidation();
		if ( ! $invalidates_on_password_change ) {
			$issues[] = __( 'Sessions may not be invalidated when password changes (stolen sessions persist)', 'wpshadow' );
		}

		// Check 4: Check for session metadata (IP, user agent tracking).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$sample_session = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->usermeta} 
				WHERE meta_key = %s 
				LIMIT 1",
				'session_tokens'
			)
		);

		if ( $sample_session ) {
			$session_data = maybe_unserialize( $sample_session );
			if ( is_array( $session_data ) ) {
				$first_session = reset( $session_data );
				if ( ! isset( $first_session['ip'] ) && ! isset( $first_session['ua'] ) ) {
					$issues[] = __( 'Session tokens do not track IP address or user agent (harder to detect hijacking)', 'wpshadow' );
				}
			}
		}

		// Check 5: Check for session revocation capability.
		$has_revocation = self::check_session_revocation();
		if ( ! $has_revocation ) {
			$issues[] = __( 'No session revocation mechanism detected (cannot terminate specific sessions)', 'wpshadow' );
		}

		// Check 6: Look for excessive active sessions.
		$excessive_sessions = self::find_users_with_excessive_sessions();
		if ( ! empty( $excessive_sessions ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				_n(
					'%d user has more than 10 active sessions',
					'%d users have more than 10 active sessions',
					count( $excessive_sessions ),
					'wpshadow'
				),
				count( $excessive_sessions )
			);
		}

		// Check 7: Check for "Destroy all other sessions" functionality.
		$has_destroy_others = has_action( 'wp_login', 'wp_destroy_other_sessions' ) || 
		                       has_filter( 'attach_session_information' );
		if ( ! $has_destroy_others ) {
			$issues[] = __( '"Destroy all other sessions" functionality may not be available', 'wpshadow' );
		}

		// If we found issues, return finding.
		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d concurrent session control issue detected',
						'%d concurrent session control issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/concurrent-session-control',
				'context'      => array(
					'issues' => $issues,
					'stats'  => array(
						'users_with_sessions' => (int) $has_session_tokens,
						'excessive_sessions'  => count( $excessive_sessions ),
					),
					'why' => __(
						'Unlimited concurrent sessions enable account sharing and make session hijacking more persistent. ' .
						'If an attacker steals a session cookie, they can maintain access even after the legitimate user ' .
						'logs in again from another device. Without session limits, compromised accounts can have dozens ' .
						'of active sessions from different locations simultaneously. NIST recommends limiting concurrent ' .
						'sessions and providing users the ability to view and terminate active sessions. When credentials ' .
						'change, all previous sessions should be immediately invalidated to prevent continued unauthorized access.',
						'wpshadow'
					),
					'recommendation' => __(
						'Implement concurrent session limits (3-5 sessions per user recommended). ' .
						'Invalidate all sessions on password change using wp_destroy_all_sessions(). ' .
						'Track session metadata (IP, user agent, login time) for anomaly detection. ' .
						'Provide users a dashboard to view and revoke active sessions. ' .
						'Consider implementing WPShadow Pro Security for advanced session management with geolocation tracking and anomaly alerts.',
						'wpshadow'
					),
				),
			);

			// Add upgrade path for WPShadow Pro Security (when available).
			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'concurrent-session-limiting',
				'session-management-setup'
			);

			return $finding;
		}

		return null;
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
