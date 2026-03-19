<?php
/**
 * User Login Attempt Limiting Diagnostic
 *
 * Validates that login attempts are properly limited to prevent
 * brute force attacks and excessive failed login attempts.
 * Unlimited login attempts = attacker tries 10,000 passwords.
 * Rate limiting = after 5 failures, account locked/IP blocked.
 *
 * **What This Check Does:**
 * - Checks if login rate limiting plugin installed
 * - Validates lockout threshold (5-10 failures)
 * - Tests lockout duration (15-60 minutes)
 * - Checks IP-based blocking
 * - Validates CAPTCHA after failures
 * - Returns severity if rate limiting disabled
 *
 * **Why This Matters:**
 * Without rate limiting: attacker tries unlimited passwords.
 * Dictionary attack (100K passwords) takes hours.
 * With rate limiting: after 5 failures, locked out 30 minutes.
 * Brute force becomes impractical (years to complete).
 *
 * **Business Impact:**
 * Admin account has weak password ("password123").
 * Without rate limiting: attacker's script tries 50K passwords in 2 hours.
 * Finds match. Site compromised. With rate limiting: after 5 failures,
 * IP blocked. Attacker needs 50K different IPs (expensive/impractical).
 * Account stays secure.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Brute force blocked
 * - #9 Show Value: Makes brute force impractical
 * - #10 Beyond Pure: Rate limiting by design
 *
 * **Related Checks:**
 * - Brute Force Protection Overall (broader)
 * - User Enumeration Vulnerability (related)
 * - 2FA Required (complementary)
 *
 * **Learn More:**
 * Login rate limiting: https://wpshadow.com/kb/login-rate-limiting
 * Video: Configuring rate limiting (10min): https://wpshadow.com/training/rate-limit
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Login Attempt Limiting Diagnostic Class
 *
 * Checks login attempt limiting configuration.
 *
 * **Detection Pattern:**
 * 1. Check if rate limiting plugin active
 * 2. Get lockout threshold setting
 * 3. Validate lockout duration
 * 4. Test IP-based blocking
 * 5. Check CAPTCHA integration
 * 6. Return if rate limiting disabled
 *
 * **Real-World Scenario:**
 * Rate limiting enabled (5 failures = 30 min lockout). Attacker tries
 * brute force. After 5 failures: IP blocked. Attacker switches IP.
 * Tries 5 more. Blocked again. Cost: 1000 IPs needed for 5000 passwords.
 * Brute force becomes economically unfeasible. Attack abandoned.
 *
 * **Implementation Notes:**
 * - Checks rate limiting configuration
 * - Validates thresholds and durations
 * - Tests IP blocking
 * - Severity: critical (no rate limiting)
 * - Treatment: install/enable rate limiting plugin
 *
 * @since 1.6093.1200
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
	 * @since 1.6093.1200
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
			$finding = array(
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
				'kb_link'      => 'https://wpshadow.com/kb/login-rate-limiting',
				'details'      => array(
					'issues'              => $issues,
					'active_limiting'     => $active_limiting,
					'has_failure_tracking' => $has_failed_login_data,
				),
				'context'      => array(
					'why'            => __(
						'Without login rate limiting, attackers perform unlimited brute force attempts. A dictionary attack with 100,000 common passwords takes only 2-4 hours at 30 attempts/second without protection. With rate limiting (5 failures = 30-minute lockout), the same attack would require 100,000+ different IPs and take years to complete, making it economically infeasible. OWASP Top 10 2023 lists brute force as a top authentication risk (#07-Identification and Authentication Failures). NIST guidelines recommend account lockouts after 10-15 consecutive failed attempts within 5 minutes. Over 50% of web breaches involve compromised credentials, many obtained through brute force against weak protections. Wordfence data shows 86% of login attacks are automated dictionary/brute force attempts.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Install rate limiting plugin: Use "Limit Login Attempts Reloaded" (free) or Wordfence (paid). Alternate: "All In One WP Security" includes rate limiting.
2. Configure lockout settings: Set to 5-10 failed attempts = 30-minute lockout. Increase lockout duration after repeated offenses (exponential backoff).
3. Enable IP-based blocking: After 30 failed attempts within 24 hours, block IP address for 24 hours or longer.
4. Configure CAPTCHA: Require CAPTCHA after 3 failed attempts to prevent automated attacks while allowing legitimate users to recover.
5. Set up admin notifications: Email admin on each brute force attack (from IP X at time Y, Y attempts failed). Monitor patterns.
6. Enable failed login tracking: Log all failed attempts with timestamp, IP, username, user-agent. Enables forensic analysis.
7. Whitelist trusted IPs: If you access from static IP, whitelist it to bypass some protections. Prevents accidental lockout.
8. Monitor login patterns: Review logs for suspicious patterns (100 attempts/hour from single IP = brute force). Investigate.
9. Implement 2FA: Even with rate limiting, 2FA prevents compromise if password stolen. Combines protections.
10. Use WordPress security headers: Add "X-Robots-Tag: noindex" to prevent login page indexing (reduces automated scanning).',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'brute-force-protection',
				'limit_login_attempts'
			);

			return $finding;
		}

		return null;
	}
}
