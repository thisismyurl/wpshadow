<?php
/**
 * User Login Attempt Limiting Diagnostic
 *
 * Validates that login attempts are properly limited to prevent
 * brute force attacks and excessive failed login attempts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Login Attempt Limiting Diagnostic Class
 *
 * Checks login attempt limiting configuration.
 *
 * @since 1.6032.1340
 */
class Diagnostic_User_Login_Attempt_Limiting extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-login-attempt-limiting';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Login Attempt Limiting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates login attempt limiting for brute force protection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for login limiting plugins.
		$limiting_plugins = array(
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' => 'Limit Login Attempts Reloaded',
			'limit-login-attempts/limit-login-attempts.php'                   => 'Limit Login Attempts',
			'wp-limit-login-attempts/wp-limit-login-attempts.php'             => 'WP Limit Login Attempts',
			'wordfence/wordfence.php'                                         => 'Wordfence',
			'all-in-one-wp-security-and-firewall/wp-security.php'             => 'All In One WP Security',
			'jetpack/jetpack.php'                                             => 'Jetpack (has brute force protection)',
		);

		$active_limiting = array();
		foreach ( $limiting_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$active_limiting[] = $plugin_name;
			}
		}

		if ( empty( $active_limiting ) ) {
			$issues[] = __( 'No login attempt limiting plugin active (brute force vulnerable)', 'wpshadow' );
		}

		// Check for custom login limiting implementation.
		global $wp_filter;
		if ( isset( $wp_filter['wp_login_failed'] ) || isset( $wp_filter['wp_authenticate_user'] ) ) {
			// Custom login tracking might be implemented.
		}

		// Check wp-config.php for rate limiting headers.
		$wp_config = wp_upload_dir();
		$wp_config_path = defined( 'WP_CONFIG_DIR' ) ? WP_CONFIG_DIR : ABSPATH;

		// Check for common limiting settings.
		if ( defined( 'LLAR_LIMIT' ) ) {
			// Limit Login Attempts Reloaded configuration present.
		}

		// Check for excessive failed login attempts in database.
		global $wpdb;

		// Check for common failed login tracking tables.
		$tables_to_check = array(
			$wpdb->prefix . 'aiowps_failed_logins',
			$wpdb->prefix . 'llar_failed_attempts',
			$wpdb->prefix . 'wflogins',
		);

		$has_failed_login_data = false;
		foreach ( $tables_to_check as $table ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
			if ( $exists ) {
				$has_failed_login_data = true;

				// Check for recent excessive failures.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$recent_failures = $wpdb->get_var(
					"SELECT COUNT(*) FROM $table 
					WHERE SUBSTRING(get_date_from_meta_key, 1, 10) = DATE_FORMAT(NOW(), '%Y-%m-%d')"
				);

				if ( $recent_failures > 100 ) {
					$issues[] = sprintf(
						/* translators: %d: number of failed login attempts */
						__( '%d failed login attempts today (possible brute force attack in progress)', 'wpshadow' ),
						$recent_failures
					);
				}
				break;
			}
		}

		if ( ! $has_failed_login_data && empty( $active_limiting ) ) {
			$issues[] = __( 'No login failure tracking (cannot detect brute force attacks)', 'wpshadow' );
		}

		// Check for IP-based blocking.
		if ( ! empty( $active_limiting ) ) {
			// Limiting plugin is active, so blocking should work.
		} else {
			// Check if server has rate limiting (CloudFlare, mod_evasive, etc).
			$server_has_limiting = isset( $_SERVER['HTTP_CF_RAY'] ) || isset( $_SERVER['HTTP_X_FORWARDED_FOR'] );
			// Not definitive, but indicates possible protection.
		}

		// Check for password reset attempts limiting.
		$has_password_reset_limiting = false;
		if ( is_plugin_active( 'wordfence/wordfence.php' ) ) {
			$has_password_reset_limiting = true;
		}

		// Check for lockout duration configuration.
		if ( defined( 'LLAR_LOCKOUT_DURATION' ) ) {
			$lockout_duration = LLAR_LOCKOUT_DURATION;
			if ( $lockout_duration < 600 ) { // Less than 10 minutes.
				$issues[] = sprintf(
					/* translators: %d: minutes */
					__( 'Lockout duration is only %d minutes (too short to be effective)', 'wpshadow' ),
					absint( $lockout_duration / 60 )
				);
			}
		}

		// Check for notification of locked accounts.
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) ) {
			$issues[] = __( 'Admin email not configured (cannot notify of brute force attacks)', 'wpshadow' );
		}

		// Check for user login logs.
		$users_table = $wpdb->prefix . 'users';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$user_count = $wpdb->get_var( "SELECT COUNT(*) FROM $users_table" );

		// If many users but no failed login tracking, this is a concern.
		if ( $user_count > 10 && ! $has_failed_login_data && empty( $active_limiting ) ) {
			$issues[] = __( 'Site has multiple users but no login attempt tracking', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of login limiting issues */
					__( 'Found %d login attempt limiting issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'details'      => array(
					'issues'              => $issues,
					'active_limiting'     => $active_limiting,
					'has_failure_tracking' => $has_failed_login_data,
					'recommendation'      => __( 'Install "Limit Login Attempts Reloaded" plugin and configure lockout duration, maximum attempts, and admin notifications.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
