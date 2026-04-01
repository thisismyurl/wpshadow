<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

use WPShadow\Core\Hook_Subscriber_Base;

/**
 * Failed Login Analyzer
 *
 * Tracks failed login attempts to detect brute force attacks and measure security impact.
 * Hooks into WordPress authentication to monitor failed logins.
 *
 * Philosophy: Show value (#9) - Demonstrate security protection effectiveness.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 0.6093.1200
 */
class Failed_Login_Analyzer extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since 0.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'wp_login_failed'   => 'record_failed_login',
			'wp_login'          => array( 'record_successful_login', 10, 2 ),
		);
	}

	/**
	 * Initialize failed login tracking (deprecated)
	 *
	 * Hooks into WordPress authentication events
	 *
	 * @deprecated1.0 Use Failed_Login_Analyzer::subscribe() instead
	 * @return     void
	 */
	public static function init(): void {
		self::subscribe();
	}

	/**
	 * Record a failed login attempt
	 *
	 * @param string $username Username or email attempted
	 * @return void
	 */
	public static function record_failed_login( string $username ): void {
		// Get current counts
		$failed_24h   = (int) \WPShadow\Core\Cache_Manager::get( 'failed_logins_24h', 'wpshadow_monitoring' );
		$failed_1h    = (int) \WPShadow\Core\Cache_Manager::get( 'failed_logins_count', 'wpshadow_monitoring' );
		$failed_30min = (int) \WPShadow\Core\Cache_Manager::get( 'recent_login_failures', 'wpshadow_monitoring' );

		// Increment counts
		++$failed_24h;
		++$failed_1h;
		++$failed_30min;

		// Update transients
		\WPShadow\Core\Cache_Manager::set( 'failed_logins_24h', $failed_24h, DAY_IN_SECONDS , 'wpshadow_monitoring');
		\WPShadow\Core\Cache_Manager::set( 'failed_logins_count', $failed_1h, HOUR_IN_SECONDS , 'wpshadow_monitoring');
		\WPShadow\Core\Cache_Manager::set( 'recent_login_failures', $failed_30min, 30 * MINUTE_IN_SECONDS , 'wpshadow_monitoring');

		// Store detailed log entry
		$log = \WPShadow\Core\Cache_Manager::get( 'failed_login_log', 'wpshadow_monitoring' );
		if ( ! is_array( $log ) ) {
			$log = array();
		}

		$log[] = array(
			'username'   => sanitize_user( $username ),
			'ip'         => self::get_client_ip(),
			'timestamp'  => time(),
			'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ), 0, 200 ) : '',
		);

		// Keep only last 100 entries
		if ( count( $log ) > 100 ) {
			$log = array_slice( $log, -100 );
		}

		\WPShadow\Core\Cache_Manager::set( 'failed_login_log', $log, DAY_IN_SECONDS , 'wpshadow_monitoring');
	}

	/**
	 * Record a successful login (for rate calculation)
	 *
	 * @param string $user_login Username
	 * @param \WP_User $user User object
	 * @return void
	 */
	public static function record_successful_login( string $user_login, $user ): void {
		// Increment successful login counter
		$successful_24h = (int) \WPShadow\Core\Cache_Manager::get( 'successful_logins_24h', 'wpshadow_monitoring' );
		++$successful_24h;
		\WPShadow\Core\Cache_Manager::set( 'successful_logins_24h', $successful_24h, DAY_IN_SECONDS , 'wpshadow_monitoring');
	}

	/**
	 * Get analysis summary
	 *
	 * Returns current failed login statistics
	 *
	 * @return array Analysis data
	 */
	public static function get_summary(): array {
		$failed_24h     = (int) \WPShadow\Core\Cache_Manager::get( 'failed_logins_24h', 'wpshadow_monitoring' );
		$failed_1h      = (int) \WPShadow\Core\Cache_Manager::get( 'failed_logins_count', 'wpshadow_monitoring' );
		$failed_30min   = (int) \WPShadow\Core\Cache_Manager::get( 'recent_login_failures', 'wpshadow_monitoring' );
		$successful_24h = (int) \WPShadow\Core\Cache_Manager::get( 'successful_logins_24h', 'wpshadow_monitoring' );
		$log            = \WPShadow\Core\Cache_Manager::get( 'failed_login_log', 'wpshadow_monitoring' );

		// Calculate attack patterns
		$unique_ips       = array();
		$unique_usernames = array();
		if ( is_array( $log ) ) {
			foreach ( $log as $entry ) {
				$unique_ips[ $entry['ip'] ]             = true;
				$unique_usernames[ $entry['username'] ] = true;
			}
		}

		return array(
			'failed_24h'                => $failed_24h,
			'failed_1h'                 => $failed_1h,
			'failed_30min'              => $failed_30min,
			'successful_24h'            => $successful_24h,
			'total_24h'                 => $failed_24h + $successful_24h,
			'failure_rate'              => $failed_24h > 0 && ( $failed_24h + $successful_24h ) > 0
				? round( ( $failed_24h / ( $failed_24h + $successful_24h ) ) * 100, 2 )
				: 0,
			'unique_ips_attacking'      => count( $unique_ips ),
			'unique_usernames_targeted' => count( $unique_usernames ),
			'is_under_attack'           => $failed_1h > 5 || $failed_30min > 10,
		);
	}

	/**
	 * Get client IP address (handles proxies)
	 *
	 * @return string IP address
	 */
	private static function get_client_ip(): string {
		$ip = '';

		if ( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			// Cloudflare
			$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			// Proxy
			$ip = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] )[0];
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			// Direct
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return sanitize_text_field( trim( $ip ) );
	}

	/**
	 * Clear all cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'failed_logins_24h', 'wpshadow_monitoring' );
		\WPShadow\Core\Cache_Manager::delete( 'failed_logins_count', 'wpshadow_monitoring' );
		\WPShadow\Core\Cache_Manager::delete( 'recent_login_failures', 'wpshadow_monitoring' );
		\WPShadow\Core\Cache_Manager::delete( 'successful_logins_24h', 'wpshadow_monitoring' );
		\WPShadow\Core\Cache_Manager::delete( 'failed_login_log', 'wpshadow_monitoring' );
	}
}
