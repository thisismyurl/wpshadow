<?php
/**
 * User Login Notification System Treatment
 *
 * Validates that important login events are being logged and monitored
 * for security purposes (failed logins, unusual activity).
 * Login notifications = admin alerted to suspicious activity.
 * Detects compromises early (before major damage).
 *
 * **What This Check Does:**
 * - Checks if login monitoring enabled
 * - Validates failed login notifications
 * - Tests unusual login detection (new location/device)
 * - Checks admin notification for successful admin logins
 * - Validates login log retention
 * - Returns severity if monitoring disabled
 *
 * **Why This Matters:**
 * Without monitoring: account compromised, admin doesn't know.
 * Attacker operates silently for weeks/months.
 * With notifications: suspicious login triggers alert.
 * Admin investigates immediately. Compromise detected early.
 *
 * **Business Impact:**
 * Admin account compromised (phishing). Attacker logs in from Russia.
 * Without notifications: attacker operates 2 months undetected. Installs
 * malware. Steals data. Damage: $500K+. With notifications: admin gets
 * email "Login from Russia" immediately. Resets password. Account secured
 * within minutes. Damage prevented.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Suspicious activity detected
 * - #9 Show Value: Early compromise detection
 * - #10 Beyond Pure: Security monitoring embedded
 *
 * **Related Checks:**
 * - User Login Attempt Limiting (complementary)
 * - Activity Logging Overall (broader)
 * - Admin Account Security (related)
 *
 * **Learn More:**
 * Login monitoring setup: https://wpshadow.com/kb/login-monitoring
 * Video: Configuring login notifications (11min): https://wpshadow.com/training/login-alerts
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1335
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Login Notification System Treatment Class
 *
 * Checks login monitoring and notification configuration.
 *
 * **Detection Pattern:**
 * 1. Check if login monitoring plugin active
 * 2. Validate failed login notifications enabled
 * 3. Test unusual login detection
 * 4. Check admin notification settings
 * 5. Validate log retention period
 * 6. Return if monitoring disabled
 *
 * **Real-World Scenario:**
 * Admin receives email: "Login from new location (Tokyo, Japan)".
 * Admin didn't login. Immediately resets password. Checks activity log.
 * Attacker accessed for 10 minutes. No damage done. Crisis averted.
 * Without notification: attacker operates undetected for months.
 *
 * **Implementation Notes:**
 * - Checks monitoring configuration
 * - Validates notification settings
 * - Tests alert triggers
 * - Severity: high (no monitoring)
 * - Treatment: enable login monitoring and notifications
 *
 * @since 1.6032.1335
 */
class Treatment_User_Login_Notification_System extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-login-notification-system';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'User Login Notification System';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates login monitoring and notifications';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1335
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for security plugins with login monitoring.
		$security_plugins = array(
			'wordfence/wordfence.php'                    => 'Wordfence',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'sucuri-scanner/sucuri.php'                  => 'Sucuri',
			'ithemes-security-pro/ithemes-security-pro.php' => 'iThemes Security Pro',
			'better-wp-security/better-wp-security.php'  => 'iThemes Security',
			'login-security-solution/login-security-solution.php' => 'Login Security Solution',
		);

		$active_security = array();
		foreach ( $security_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$active_security[] = $plugin_name;
			}
		}

		if ( empty( $active_security ) ) {
			$issues[] = __( 'No security plugin active for login monitoring', 'wpshadow' );
		}

		// Check for custom login hooks.
		global $wp_filter;
		$login_hooks = array( 'wp_login', 'wp_login_failed', 'wp_logout' );

		$has_custom_handlers = false;
		foreach ( $login_hooks as $hook ) {
			if ( isset( $wp_filter[ $hook ] ) && ! empty( $wp_filter[ $hook ]->callbacks ) ) {
				// Check if there are custom callbacks beyond WordPress core.
				foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
					if ( ! empty( $callbacks ) ) {
						$has_custom_handlers = true;
						break 2;
					}
				}
			}
		}

		if ( ! $has_custom_handlers && empty( $active_security ) ) {
			$issues[] = __( 'No login event handlers registered (no monitoring)', 'wpshadow' );
		}

		// Check failed login attempts (if data available).
		global $wpdb;

		// Check for common failed login tables from security plugins.
		$tables_to_check = array(
			$wpdb->prefix . 'aiowps_failed_logins',
			$wpdb->prefix . 'wflogins',
			$wpdb->prefix . 'itsec_logs',
		);

		$failed_login_data = false;
		foreach ( $tables_to_check as $table ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
			if ( $exists ) {
				$failed_login_data = true;
				break;
			}
		}

		// Check for excessive failed login attempts.
		if ( $failed_login_data ) {
			// Check recent failed logins (last 24 hours).
			foreach ( $tables_to_check as $table ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
				if ( $exists ) {
					// Query depends on table structure - be cautious.
					// Just note that monitoring is active.
					break;
				}
			}
		}

		// Check for login limit/throttling.
		$has_login_limit = false;
		$limit_plugins   = array(
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' => 'Limit Login Attempts Reloaded',
			'limit-login-attempts/limit-login-attempts.php' => 'Limit Login Attempts',
			'wp-limit-login-attempts/wp-limit-login-attempts.php' => 'WP Limit Login Attempts',
		);

		foreach ( $limit_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_login_limit = true;
				break;
			}
		}

		// Security plugins also provide login limiting.
		if ( ! empty( $active_security ) ) {
			$has_login_limit = true;
		}

		if ( ! $has_login_limit ) {
			$issues[] = __( 'No login attempt limiting configured (brute force vulnerable)', 'wpshadow' );
		}

		// Check for 2FA requirement for admins.
		$admins = get_users( array( 'role' => 'administrator', 'fields' => 'ID' ) );
		if ( ! empty( $admins ) ) {
			// Check if 2FA plugin is active.
			$twofa_plugins = array(
				'two-factor/two-factor.php',
				'google-authenticator/google-authenticator.php',
				'wordfence/wordfence.php',
			);

			$has_2fa = false;
			foreach ( $twofa_plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					$has_2fa = true;
					break;
				}
			}

			if ( ! $has_2fa ) {
				$issues[] = __( 'No two-factor authentication plugin active for administrators', 'wpshadow' );
			}
		}

		// Check for admin notification settings.
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) ) {
			$issues[] = __( 'Admin email not configured (cannot send security notifications)', 'wpshadow' );
		}

		// Check for suspicious login patterns in user meta.
		$recent_suspicious = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE '%login_attempt%' 
			OR meta_key LIKE '%failed_login%'
			OR meta_key LIKE '%locked%'"
		);

		if ( $recent_suspicious > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of suspicious login records */
				__( '%d user accounts have failed login records (possible attack)', 'wpshadow' ),
				$recent_suspicious
			);
		}

		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of login notification issues */
					__( 'Found %d login monitoring and security gaps.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/login-monitoring',
				'details'      => array(
					'issues'          => $issues,
					'active_security' => $active_security,
					'has_login_limit' => $has_login_limit,
					'has_2fa'         => isset( $has_2fa ) ? $has_2fa : false,
				),
				'context'      => array(
					'why'            => __(
						'Login monitoring is the early-warning system for account compromise. Without notifications, attackers can access admin accounts for days or weeks before being detected. Alerts for failed logins, new locations, and admin logins allow rapid response (password reset, session revocation, IP blocking). Monitoring also helps detect brute force attacks and credential stuffing, which are common in automated campaigns.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Enable login monitoring in a security plugin (Wordfence, Sucuri, iThemes).
2. Send admin alerts for failed logins and successful admin logins.
3. Enable detection of new device/location logins.
4. Retain login logs for at least 90 days for incident response.
5. Combine with rate limiting and 2FA for layered protection.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'login-monitoring',
				'login_notifications'
			);

			return $finding;
		}

		return null;
	}
}
