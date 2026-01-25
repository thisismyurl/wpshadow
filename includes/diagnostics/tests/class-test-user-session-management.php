<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: User Session Management
 *
 * Monitors user session security and concurrent login limits.
 * Multiple simultaneous sessions increase account hijacking risk.
 *
 * @since 1.2.0
 */
class Test_User_Session_Management extends Diagnostic_Base {


	/**
	 * Check session management
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$sessions = self::analyze_user_sessions();

		if ( empty( $sessions['excessive_sessions'] ) ) {
			return null;
		}

		$threat = min( 75, count( $sessions['excessive_sessions'] ) * 10 );

		return array(
			'threat_level'  => $threat,
			'threat_color'  => 'orange',
			'passed'        => false,
			'issue'         => sprintf(
				'%d user accounts with multiple concurrent sessions',
				count( $sessions['excessive_sessions'] )
			),
			'metadata'      => $sessions,
			'kb_link'       => 'https://wpshadow.com/kb/user-session-security/',
			'training_link' => 'https://wpshadow.com/training/wordpress-user-management/',
		);
	}

	/**
	 * Guardian Sub-Test: Concurrent session analysis
	 *
	 * @return array Test result
	 */
	public static function test_concurrent_sessions(): array {
		$sessions = self::analyze_user_sessions();

		return array(
			'test_name'          => 'Concurrent Session Analysis',
			'total_sessions'     => $sessions['total_sessions'] ?? 0,
			'excessive_sessions' => count( $sessions['excessive_sessions'] ?? array() ),
			'passed'             => empty( $sessions['excessive_sessions'] ),
			'description'        => empty( $sessions['excessive_sessions'] ) ? 'User sessions appear normal' : sprintf( '%d accounts have excessive concurrent sessions', count( $sessions['excessive_sessions'] ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Session token uniqueness
	 *
	 * @return array Test result
	 */
	public static function test_session_token_uniqueness(): array {
		global $wpdb;

		// Get all active user sessions
		$sessions_table = $wpdb->prefix . 'user_meta';
		$results        = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key, COUNT(*) as cnt FROM {$sessions_table} WHERE meta_key LIKE %s GROUP BY meta_key",
				'%wp_session%'
			)
		);

		$duplicate_tokens = 0;
		foreach ( $results as $row ) {
			if ( $row->cnt > 1 ) {
				$duplicate_tokens += $row->cnt;
			}
		}

		return array(
			'test_name'        => 'Session Token Uniqueness',
			'duplicate_tokens' => $duplicate_tokens,
			'passed'           => $duplicate_tokens === 0,
			'description'      => $duplicate_tokens === 0 ? 'All session tokens are unique' : sprintf( '%d duplicate session tokens detected', $duplicate_tokens ),
		);
	}

	/**
	 * Guardian Sub-Test: Session timeout configuration
	 *
	 * @return array Test result
	 */
	public static function test_session_timeout(): array {
		$cookie_expire = intval( apply_filters( 'auth_cookie_expiration', 2 * DAY_IN_SECONDS ) );
		$is_secure     = is_ssl();

		return array(
			'test_name'         => 'Session Timeout Configuration',
			'cookie_expiration' => $cookie_expire,
			'days'              => round( $cookie_expire / DAY_IN_SECONDS ),
			'is_secure'         => $is_secure,
			'passed'            => $cookie_expire <= 14 * DAY_IN_SECONDS && $is_secure,
			'description'       => sprintf( 'Session timeout: %d days, HTTPS: %s', round( $cookie_expire / DAY_IN_SECONDS ), $is_secure ? 'Yes' : 'No' ),
		);
	}

	/**
	 * Guardian Sub-Test: Admin user account checks
	 *
	 * @return array Test result
	 */
	public static function test_admin_accounts(): array {
		$admins = get_users( array( 'role' => 'administrator' ) );

		$concerns = array();
		foreach ( $admins as $admin ) {
			// Check for weak usernames
			if ( in_array( strtolower( $admin->user_login ), array( 'admin', 'administrator', 'root', 'test' ), true ) ) {
				$concerns[] = $admin->user_login;
			}
		}

		return array(
			'test_name'      => 'Admin Account Security',
			'admin_count'    => count( $admins ),
			'weak_usernames' => $concerns,
			'passed'         => empty( $concerns ),
			'description'    => empty( $concerns ) ? sprintf( '%d admin accounts with secure usernames', count( $admins ) ) : sprintf( '%d admin accounts with weak usernames', count( $concerns ) ),
		);
	}

	/**
	 * Analyze user sessions
	 *
	 * @return array Session analysis
	 */
	private static function analyze_user_sessions(): array {
		global $wpdb;

		$sessions_table = $wpdb->prefix . 'usermeta';

		// Count user sessions
		$session_results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, COUNT(*) as session_count FROM {$sessions_table} WHERE meta_key LIKE %s GROUP BY user_id HAVING session_count > 1",
				'%wp_session_expires%'
			)
		);

		$excessive_sessions = array();
		$total_sessions     = 0;

		foreach ( $session_results as $result ) {
			$total_sessions += $result->session_count;
			if ( $result->session_count > 3 ) {
				$excessive_sessions[] = array(
					'user_id'       => $result->user_id,
					'session_count' => $result->session_count,
				);
			}
		}

		return array(
			'total_sessions'     => $total_sessions,
			'excessive_sessions' => $excessive_sessions,
		);
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'User Session Management';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Monitors user session security and concurrent login limits';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Security';
	}
}
