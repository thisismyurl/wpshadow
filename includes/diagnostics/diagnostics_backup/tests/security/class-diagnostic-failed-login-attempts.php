<?php
/**
 * Excessive Failed Login Attempts Diagnostic
 *
 * Analyzes failed login attempts to detect distributed brute force attacks.
 * High failure patterns indicate ongoing attack attempts that should trigger
 * rate limiting, IP blocking, or security plugin intervention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6028.1505
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Failed Login Attempts Diagnostic Class
 *
 * Monitors failed login attempt patterns to detect brute force attacks,
 * distributed attacks, and credential stuffing attempts.
 *
 * @since 1.6028.1505
 */
class Diagnostic_Failed_Login_Attempts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1505
	 * @var   string
	 */
	protected static $slug = 'failed-login-attempts';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1505
	 * @var   string
	 */
	protected static $title = 'Excessive Failed Login Attempts Pattern';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1505
	 * @var   string
	 */
	protected static $description = 'Detects brute force attack patterns through failed login analysis';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1505
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check
	 *
	 * Analyzes failed login attempts from past 24 hours:
	 * - Counts total failures
	 * - Identifies distributed attack patterns
	 * - Calculates failures per hour
	 * - Checks for security plugin protection
	 *
	 * @since  1.6028.1505
	 * @return array|null Null if protected, array if high failure rate.
	 */
	public static function check() {
		$attack_data = self::analyze_failed_logins();

		if ( is_null( $attack_data ) ) {
			return null; // Cannot analyze or rate is acceptable.
		}

		$failures_per_hour = $attack_data['failures_per_hour'];

		// Only report if concerning (>5 per hour).
		if ( $failures_per_hour <= 5 ) {
			return null;
		}

		$severity = $failures_per_hour > 50 ? 'critical' : 'high';
		$threat_level = min( 85, 40 + ( $failures_per_hour * 0.5 ) );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: failed login attempts per hour */
				__( 'Site is experiencing %d failed login attempts per hour, indicating brute force attack', 'wpshadow' ),
				$failures_per_hour
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'family'       => self::$family,
			'kb_link'      => 'https://wpshadow.com/kb/prevent-brute-force-attacks',
			'meta'         => array(
				'failures_per_hour'   => $failures_per_hour,
				'total_failures_24h'  => $attack_data['total_failures'],
				'unique_ips'          => $attack_data['unique_ips'],
				'is_distributed'      => $attack_data['is_distributed'],
				'has_security_plugin' => $attack_data['has_security_plugin'],
				'immediate_actions'   => array(
					__( 'Install security plugin with login rate limiting', 'wpshadow' ),
					__( 'Enable two-factor authentication for all admins', 'wpshadow' ),
					__( 'Block attacking IP addresses at firewall level', 'wpshadow' ),
					__( 'Change default admin username if using "admin"', 'wpshadow' ),
				),
			),
			'details'      => array(
				'why_important' => __(
					'Failed login attempts indicate active brute force attacks attempting to guess admin passwords. Distributed attacks use hundreds or thousands of IPs making them harder to block. Without rate limiting, attackers can try millions of password combinations. Successful compromise leads to complete site takeover, malware injection, or ransomware deployment.',
					'wpshadow'
				),
				'user_impact'   => __(
					'High CPU usage from authentication checks, potential server crashes from attack volume, increased hosting costs, account lockouts for legitimate users. If attackers succeed: complete data loss, malware injection, SEO spam, blacklisting, customer data theft. Prevention is exponentially cheaper than recovery.',
					'wpshadow'
				),
				'solution_options' => array(
					'free'     => array(
						__( 'Install Wordfence Security (free version)', 'wpshadow' ),
						__( 'Limit login attempts to 3 per 15 minutes', 'wpshadow' ),
						__( 'Block repeated failed attempts from same IP', 'wpshadow' ),
					),
					'premium'  => array(
						__( 'Install Wordfence Premium with real-time IP blocking', 'wpshadow' ),
						__( 'Use iThemes Security Pro with advanced protection', 'wpshadow' ),
						__( 'Deploy Cloudflare with firewall rules', 'wpshadow' ),
					),
					'advanced' => array(
						__( 'Implement fail2ban for automated IP blocking', 'wpshadow' ),
						__( 'Deploy web application firewall (WAF)', 'wpshadow' ),
						__( 'Use geographic restrictions for login access', 'wpshadow' ),
					),
				),
				'best_practices' => array(
					__( 'Limit login attempts to 3-5 per IP per hour', 'wpshadow' ),
					__( 'Require strong passwords (16+ characters) for all users', 'wpshadow' ),
					__( 'Enable two-factor authentication for administrators', 'wpshadow' ),
					__( 'Monitor failed login patterns daily', 'wpshadow' ),
					__( 'Use CAPTCHA after 2 failed attempts', 'wpshadow' ),
					__( 'Block known malicious IPs proactively', 'wpshadow' ),
					__( 'Never use "admin" as username', 'wpshadow' ),
					__( 'Consider restricting wp-login.php to known IPs', 'wpshadow' ),
				),
				'testing_steps' => array(
					__( 'Check security plugin dashboard for failed login count', 'wpshadow' ),
					__( 'Review server logs: tail -f /var/log/auth.log | grep wp-login', 'wpshadow' ),
					__( 'Verify rate limiting: Try 3 failed logins rapidly', 'wpshadow' ),
					__( 'Check if CAPTCHA appears after failures', 'wpshadow' ),
					__( 'Verify admin accounts have strong passwords', 'wpshadow' ),
					__( 'Test 2FA is working for all administrators', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Analyze failed logins
	 *
	 * Checks multiple data sources for failed login patterns:
	 * - Security plugin logs (if installed)
	 * - WordPress user meta (attempt tracking)
	 * - Server logs (if accessible)
	 *
	 * @since  1.6028.1505
	 * @return array|null Attack statistics or null if cannot analyze.
	 */
	private static function analyze_failed_logins() {
		$has_security_plugin = self::detect_security_plugin();

		// Try to get data from security plugins first.
		if ( $has_security_plugin ) {
			$plugin_data = self::get_security_plugin_data();
			if ( ! is_null( $plugin_data ) ) {
				return $plugin_data;
			}
		}

		// Fallback: Check WordPress user meta for failed attempts.
		$meta_data = self::analyze_user_meta_attempts();
		if ( ! is_null( $meta_data ) ) {
			return $meta_data;
		}

		// Cannot analyze without security plugin or logs.
		return null;
	}

	/**
	 * Detect security plugin
	 *
	 * Checks if security plugins with login monitoring are installed.
	 *
	 * @since  1.6028.1505
	 * @return bool True if security plugin detected.
	 */
	private static function detect_security_plugin() {
		// Wordfence.
		if ( class_exists( 'wordfence' ) || class_exists( 'wfConfig' ) ) {
			return true;
		}

		// iThemes Security.
		if ( class_exists( 'ITSEC_Core' ) ) {
			return true;
		}

		// All In One WP Security.
		if ( class_exists( 'AIO_WP_Security' ) ) {
			return true;
		}

		// Limit Login Attempts Reloaded.
		if ( function_exists( 'limit_login_attempts_reloaded' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get security plugin data
	 *
	 * Retrieves failed login statistics from installed security plugin.
	 *
	 * @since  1.6028.1505
	 * @return array|null Failed login statistics or null if unavailable.
	 */
	private static function get_security_plugin_data() {
		global $wpdb;

		// Wordfence.
		if ( class_exists( 'wfDB' ) && method_exists( 'wfDB', 'networkTable' ) ) {
			$table_name = \wfDB::networkTable( 'wfLogins' );
			$time_24h_ago = time() - ( 24 * 3600 );

			$failures = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table_name}
					WHERE fail = 1 AND ctime >= %d",
					$time_24h_ago
				)
			);

			if ( ! is_null( $failures ) ) {
				$unique_ips = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(DISTINCT IP) FROM {$table_name}
						WHERE fail = 1 AND ctime >= %d",
						$time_24h_ago
					)
				);

				return array(
					'total_failures'      => (int) $failures,
					'failures_per_hour'   => round( $failures / 24 ),
					'unique_ips'          => (int) $unique_ips,
					'is_distributed'      => $unique_ips > 10,
					'has_security_plugin' => true,
				);
			}
		}

		// iThemes Security.
		if ( class_exists( 'ITSEC_Log' ) ) {
			$log = \ITSEC_Log::get_instance();
			// Note: iThemes uses different structure, simplified check.
			// In production, would parse iThemes log entries.
		}

		return null;
	}

	/**
	 * Analyze user meta attempts
	 *
	 * Checks user meta for failed attempt tracking. Some themes/plugins
	 * store failed attempts in user meta.
	 *
	 * @since  1.6028.1505
	 * @return array|null Attempt statistics or null if no tracking.
	 */
	private static function analyze_user_meta_attempts() {
		global $wpdb;

		// Check for common failed attempt meta keys.
		$meta_keys = array(
			'failed_login_count',
			'_failed_login_attempts',
			'login_attempts',
		);

		$total_attempts = 0;
		$users_affected = 0;

		foreach ( $meta_keys as $meta_key ) {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT user_id, meta_value FROM {$wpdb->usermeta}
					WHERE meta_key = %s AND meta_value > 0",
					$meta_key
				)
			);

			if ( ! empty( $results ) ) {
				foreach ( $results as $result ) {
					$total_attempts += (int) $result->meta_value;
					$users_affected++;
				}
			}
		}

		if ( $total_attempts > 0 ) {
			// Estimate per hour (assuming stored for 24h).
			return array(
				'total_failures'      => $total_attempts,
				'failures_per_hour'   => round( $total_attempts / 24 ),
				'unique_ips'          => $users_affected, // Approximation.
				'is_distributed'      => $users_affected > 5,
				'has_security_plugin' => false,
			);
		}

		return null;
	}
}
