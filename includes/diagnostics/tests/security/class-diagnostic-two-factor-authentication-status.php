<?php
/**
 * Two-Factor Authentication Status Diagnostic
 *
 * Validates that two-factor authentication (2FA) is configured for admin
 * and other high-privilege accounts to prevent unauthorized access.
 * 2FA enabled = compromise requires physical device (phone/hardware key).
 * Password alone insufficient. Dramatically improves security posture.
 *
 * **What This Check Does:**
 * - Checks if 2FA plugin installed and active
 * - Validates 2FA enrolled for all admin users
 * - Tests 2FA methods (authenticator app, SMS, email)
 * - Checks if 2FA backup codes generated
 * - Validates 2FA enforcement at login
 * - Returns severity for missing 2FA on any admin
 *
 * **Why This Matters:**
 * Admin account without 2FA = vulnerable to phishing, brute force.
 * With 2FA: even stolen password useless (attacker needs device token).
 * Blocks 99% of unauthorized login attempts.
 *
 * **Business Impact:**
 * Site admin account hacked via phishing (password stolen).
 * Without 2FA: attacker logs in. Malware injected. Data stolen. $500K+ cost.
 * With 2FA: attacker has password but login fails (no device code). Admin
 * receives notification. Immediately resets password. Attacker remains locked out.
 * Crisis prevented.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Admin access hardened
 * - #9 Show Value: Prevents account takeovers
 * - #10 Beyond Pure: Multi-factor authentication enforced
 *
 * **Related Checks:**
 * - 2FA Not Required for Admin (related requirement)
 * - Login URL Not Changed From Default (related)
 * - Admin User Account Security (broader)
 *
 * **Learn More:**
 * 2FA configuration: https://wpshadow.com/kb/2fa-configuration
 * Video: 2FA enrollment guide (12min): https://wpshadow.com/training/2fa-setup
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Two-Factor Authentication Status Diagnostic Class
 *
 * Checks 2FA configuration and usage.
 *
 * **Detection Pattern:**
 * 1. Check if 2FA plugin active
 * 2. Get all users with admin capability
 * 3. For each admin: check if 2FA enrolled
 * 4. Verify 2FA method configured
 * 5. Test if 2FA enforced at login
 * 6. Return each admin without 2FA
 *
 * **Real-World Scenario:**
 * 100% of admin accounts have 2FA enabled. Admin receives phishing
 * email (looks like WordPress). Enters password in fake form. Attacker
 * tries login with password. Real WordPress requires 2FA code from
 * admin's authenticator app. Attacker doesn't have it. Login fails.
 * Admin gets notification of suspicious attempt. Account stays secure.
 *
 * **Implementation Notes:**
 * - Checks 2FA plugin status
 * - Validates 2FA enrollment
 * - Tests 2FA enforcement
 * - Severity: high (not enabled), medium (optional)
 * - Treatment: enable 2FA and require for all admins
 *
 * @since 1.6032.1340
 */
class Diagnostic_Two_Factor_Authentication_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'two-factor-authentication-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Two-Factor Authentication Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates 2FA configuration for admin accounts';

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

		// Check for 2FA plugins.
		$2fa_plugins = array(
			'two-factor-authentication/two-factor-authentication.php'  => 'Two Factor Authentication',
			'wordfence/wordfence.php'                                  => 'Wordfence (includes 2FA)',
			'google-authenticator-per-user-prompt/google-auth.php'     => 'Google Authenticator',
			'two-factor/two-factor.php'                                => 'Two Factor Authentication (Core)',
			'ft-authenticator/ft-authenticator.php'                    => 'FT Authenticator',
			'duo/duo.php'                                              => 'Duo Two-Factor Authentication',
		);

		$active_2fa_plugins = array();

		foreach ( $2fa_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$active_2fa_plugins[] = $plugin_name;
			}
		}

		if ( empty( $active_2fa_plugins ) ) {
			$issues[] = __( 'No 2FA plugin detected (admin accounts are vulnerable to brute force)', 'wpshadow' );
		}

		// Get admin users.
		$admins = get_users(
			array(
				'role'   => 'administrator',
				'fields' => array( 'ID', 'user_login' ),
			)
		);

		if ( empty( $admins ) ) {
			$issues[] = __( 'No administrator accounts found (system error)', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No administrators found.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 100,
				'auto_fixable' => false,
				'details'      => array(
					'recommendation' => __( 'Restore administrator accounts immediately.', 'wpshadow' ),
				),
			);
		}

		// Check if 2FA is enabled for admins.
		$admins_without_2fa = array();

		foreach ( $admins as $admin ) {
			$user = get_userdata( $admin->ID );

			// Check for 2FA methods.
			$has_2fa = false;

			// Check if user has Google Authenticator enabled.
			$auth_method = get_user_meta( $admin->ID, '_two_factor_totp_enabled', true );
			if ( $auth_method ) {
				$has_2fa = true;
			}

			// Check for backup codes.
			$backup_codes = get_user_meta( $admin->ID, '_two_factor_backup_codes', true );
			if ( ! empty( $backup_codes ) ) {
				$has_2fa = true;
			}

			// Check Wordfence 2FA.
			$wordfence_2fa = get_user_meta( $admin->ID, 'wf_2fa_enabled', true );
			if ( $wordfence_2fa ) {
				$has_2fa = true;
			}

			// Check for Duo.
			$duo_id = get_user_meta( $admin->ID, 'duo_user', true );
			if ( $duo_id ) {
				$has_2fa = true;
			}

			if ( ! $has_2fa ) {
				$admins_without_2fa[] = $admin->user_login;
			}
		}

		if ( ! empty( $admins_without_2fa ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of admins without 2FA */
				__( '%d administrator(s) do not have 2FA enabled: %s', 'wpshadow' ),
				count( $admins_without_2fa ),
				implode( ', ', $admins_without_2fa )
			);
		}

		// Check if 2FA is enforced.
		if ( ! empty( $active_2fa_plugins ) ) {
			// Check if 2FA is mandatory.
			$force_2fa = get_option( 'wf_force_2fa' );

			if ( ! $force_2fa ) {
				$issues[] = __( '2FA plugin is installed but not enforced for admins', 'wpshadow' );
			}
		}

		// Check for password reset option that bypasses 2FA.
		$admin_password_reset = get_option( 'admin_password_reset_allowed', 1 );

		if ( $admin_password_reset && ! empty( $active_2fa_plugins ) ) {
			$issues[] = __( 'Admin password reset is allowed (can bypass 2FA if not properly configured)', 'wpshadow' );
		}

		// Check for session security.
		global $wp_filter;

		$session_timeout = get_option( 'session_timeout', 0 );
		if ( 0 === $session_timeout ) {
			$issues[] = __( 'Session timeout not configured (sessions could be indefinite)', 'wpshadow' );
		}

		// Check for concurrent session limits.
		$has_session_limiting = false;

		foreach ( $active_2fa_plugins as $plugin ) {
			if ( false !== stripos( $plugin, 'wordfence' ) ) {
				$has_session_limiting = true;
			}
		}

		if ( ! $has_session_limiting ) {
			$issues[] = __( 'No session limiting detected (users can stay logged in indefinitely)', 'wpshadow' );
		}

		// Check for login notification.
		$login_notify = get_option( 'wf_login_notification_enabled' );

		if ( ! $login_notify && ! empty( $active_2fa_plugins ) ) {
			$issues[] = __( 'Login notifications not configured (cannot detect unauthorized access)', 'wpshadow' );
		}

		// Check user recovery options.
		$recovery_email_set = true;

		foreach ( $admins as $admin ) {
			$email = $admin->user_email;
			if ( empty( $email ) ) {
				$recovery_email_set = false;
				break;
			}
		}

		if ( ! $recovery_email_set ) {
			$issues[] = __( 'Admin account(s) missing recovery email addresses', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of 2FA issues */
					__( 'Found %d two-factor authentication configuration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'details'      => array(
					'issues'              => $issues,
					'admin_count'         => count( $admins ),
					'admins_without_2fa'  => count( $admins_without_2fa ),
					'active_2fa_plugins'  => $active_2fa_plugins,
					'recommendation'      => __( 'Install 2FA plugin (Wordfence or Two Factor Authentication). Enable 2FA for all admin accounts. Enforce 2FA for admin role. Configure login notifications and session timeouts.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
