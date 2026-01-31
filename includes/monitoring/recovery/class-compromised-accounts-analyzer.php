<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Compromised Accounts Analyzer
 *
 * Monitors user accounts for signs of compromise including suspicious login patterns,
 * password changes, privilege escalations, and unusual activity.
 *
 * Philosophy: Show value (#9) - Protect sites from compromised accounts.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.2601.2200
 */
class Compromised_Accounts_Analyzer {

	/**
	 * Initialize account monitoring
	 *
	 * @return void
	 */
	public static function init(): void {
		// Track suspicious activities
		add_action( 'wp_login', array( __CLASS__, 'track_login' ), 10, 2 );
		add_action( 'profile_update', array( __CLASS__, 'track_profile_update' ), 10, 2 );
		add_action( 'set_user_role', array( __CLASS__, 'track_role_change' ), 10, 3 );
		add_action( 'wp_update_user', array( __CLASS__, 'track_user_update' ) );

		// Run hourly analysis
		if ( ! wp_next_scheduled( 'wpshadow_analyze_compromised_accounts' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpshadow_analyze_compromised_accounts' );
		}
		add_action( 'wpshadow_analyze_compromised_accounts', array( __CLASS__, 'analyze' ) );
	}

	/**
	 * Track user login
	 *
	 * @param string $user_login Username
	 * @param \WP_User $user User object
	 * @return void
	 */
	public static function track_login( string $user_login, $user ): void {
		// Get login history
		$history = \WPShadow\Core\Cache_Manager::get(
			'login_history',
			'wpshadow_monitoring'
		);
		if ( ! is_array( $history ) ) {
			$history = array();
		}

		$user_id = $user->ID;
		if ( ! isset( $history[ $user_id ] ) ) {
			$history[ $user_id ] = array();
		}

		$history[ $user_id ][] = array(
			'timestamp'  => time(),
			'ip'         => self::get_client_ip(),
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
		);

		// Keep only last 50 logins per user
		if ( count( $history[ $user_id ] ) > 50 ) {
			$history[ $user_id ] = array_slice( $history[ $user_id ], -50 );
		}

		\WPShadow\Core\Cache_Manager::set(
			'login_history',
			$history,
			'wpshadow_monitoring',
			WEEK_IN_SECONDS
		);
	}

	/**
	 * Track profile updates
	 *
	 * @param int $user_id User ID
	 * @param \WP_User $old_user_data Old user data
	 * @return void
	 */
	public static function track_profile_update( int $user_id, $old_user_data ): void {
		$suspicious_events = get_transient( 'wpshadow_suspicious_account_events' );
		if ( ! is_array( $suspicious_events ) ) {
			$suspicious_events = array();
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}

		// Check for email change
		if ( $old_user_data->user_email !== $user->user_email ) {
			$suspicious_events[] = array(
				'user_id'   => $user_id,
				'type'      => 'email_change',
				'timestamp' => time(),
				'old_value' => $old_user_data->user_email,
				'new_value' => $user->user_email,
				'ip'        => self::get_client_ip(),
			);
		}

		set_transient( 'wpshadow_suspicious_account_events', $suspicious_events, WEEK_IN_SECONDS );
	}

	/**
	 * Track role changes
	 *
	 * @param int $user_id User ID
	 * @param string $role New role
	 * @param array $old_roles Old roles
	 * @return void
	 */
	public static function track_role_change( int $user_id, string $role, array $old_roles ): void {
		$suspicious_events = get_transient( 'wpshadow_suspicious_account_events' );
		if ( ! is_array( $suspicious_events ) ) {
			$suspicious_events = array();
		}

		// Check for privilege escalation
		$high_privilege_roles = array( 'administrator', 'super_admin', 'editor' );
		$is_escalation        = in_array( $role, $high_privilege_roles, true ) &&
						! array_intersect( $old_roles, $high_privilege_roles );

		if ( $is_escalation ) {
			$suspicious_events[] = array(
				'user_id'   => $user_id,
				'type'      => 'privilege_escalation',
				'timestamp' => time(),
				'old_roles' => $old_roles,
				'new_role'  => $role,
				'ip'        => self::get_client_ip(),
			);
		}

		set_transient( 'wpshadow_suspicious_account_events', $suspicious_events, WEEK_IN_SECONDS );
	}

	/**
	 * Track user updates
	 *
	 * @param int $user_id User ID
	 * @return void
	 */
	public static function track_user_update( int $user_id ): void {
		// Track password changes
		$last_pass_change = get_user_meta( $user_id, 'wpshadow_last_password_change', true );
		$current_pass     = get_userdata( $user_id )->user_pass ?? '';
		$stored_pass      = get_user_meta( $user_id, 'wpshadow_stored_password_hash', true );

		if ( $stored_pass && $stored_pass !== $current_pass ) {
			// Password changed
			$suspicious_events = get_transient( 'wpshadow_suspicious_account_events' );
			if ( ! is_array( $suspicious_events ) ) {
				$suspicious_events = array();
			}

			$suspicious_events[] = array(
				'user_id'   => $user_id,
				'type'      => 'password_change',
				'timestamp' => time(),
				'ip'        => self::get_client_ip(),
			);

			set_transient( 'wpshadow_suspicious_account_events', $suspicious_events, WEEK_IN_SECONDS );
			update_user_meta( $user_id, 'wpshadow_last_password_change', time() );
		}

		update_user_meta( $user_id, 'wpshadow_stored_password_hash', $current_pass );
	}

	/**
	 * Analyze for compromised accounts
	 *
	 * @return array Analysis results
	 */
	public static function analyze(): array {
		$results = array(
			'suspicious_accounts'    => array(),
			'suspicious_event_count' => 0,
			'high_risk_accounts'     => array(),
		);

		$login_history     = get_transient( 'wpshadow_login_history' );
		$suspicious_events = get_transient( 'wpshadow_suspicious_account_events' );

		// Analyze login patterns
		if ( is_array( $login_history ) ) {
			foreach ( $login_history as $user_id => $logins ) {
				$risk_score = 0;
				$reasons    = array();

				// Check for multiple IPs
				$unique_ips = array_unique( array_column( $logins, 'ip' ) );
				if ( count( $unique_ips ) > 10 ) {
					$risk_score += 3;
					$reasons[]   = 'Multiple IPs (' . count( $unique_ips ) . ')';
				}

				// Check for rapid logins
				$recent_logins = array_filter(
					$logins,
					function ( $login ) {
						return $login['timestamp'] > ( time() - HOUR_IN_SECONDS );
					}
				);
				if ( count( $recent_logins ) > 10 ) {
					$risk_score += 2;
					$reasons[]   = 'Rapid logins (' . count( $recent_logins ) . ' in 1 hour)';
				}

				if ( $risk_score > 0 ) {
					$results['suspicious_accounts'][ $user_id ] = array(
						'risk_score' => $risk_score,
						'reasons'    => $reasons,
					);
				}
			}
		}

		// Analyze suspicious events
		if ( is_array( $suspicious_events ) ) {
			$results['suspicious_event_count'] = count( $suspicious_events );

			// Group by user
			$events_by_user = array();
			foreach ( $suspicious_events as $event ) {
				$user_id = $event['user_id'];
				if ( ! isset( $events_by_user[ $user_id ] ) ) {
					$events_by_user[ $user_id ] = array();
				}
				$events_by_user[ $user_id ][] = $event;
			}

			// Identify high risk accounts
			foreach ( $events_by_user as $user_id => $events ) {
				if ( count( $events ) >= 3 ) {
					$results['high_risk_accounts'][] = $user_id;
				}
			}
		}

		// Set transient for diagnostic
		set_transient( 'wpshadow_compromised_account_analysis', $results, HOUR_IN_SECONDS );

		return $results;
	}

	/**
	 * Get client IP address
	 *
	 * @return string IP address
	 */
	private static function get_client_ip(): string {
		$ip_keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' );
		foreach ( $ip_keys as $key ) {
			if ( isset( $_SERVER[ $key ] ) ) {
				$ip = $_SERVER[ $key ];
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = trim( explode( ',', $ip )[0] );
				}
				return $ip;
			}
		}
		return 'unknown';
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		delete_transient( 'wpshadow_login_history' );
		delete_transient( 'wpshadow_suspicious_account_events' );
		delete_transient( 'wpshadow_compromised_account_analysis' );
	}
}
