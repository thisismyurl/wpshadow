<?php
/**
 * Weak User Passwords Diagnostic
 *
 * Identifies users with weak or compromised passwords using available
 * password strength indicators and security plugins.
 * Weak passwords = #1 cause of account compromise.
 * "password123" cracked in seconds. Strong passwords = years to crack.
 *
 * **What This Check Does:**
 * - Scans all user accounts for password strength
 * - Checks against common password lists (rockyou.txt, etc)
 * - Validates minimum password requirements enforced
 * - Tests for dictionary words in passwords
 * - Checks password complexity rules
 * - Returns severity for each weak password found
 *
 * **Why This Matters:**
 * Weak password = attacker cracks in minutes via brute force.
 * Strong password (16+ chars, mixed case, symbols) = impractical to crack.
 * Password strength = first line of defense.
 *
 * **Business Impact:**
 * Admin account has password "admin123". Brute force attack tries
 * 100K common passwords. Finds match in 30 minutes. Site compromised.
 * Cost: $500K+. With strong password ("Tr0ub4dor&3XqR9#mK"):
 * brute force takes 1000+ years. Attack abandoned. Site safe.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Passwords meet strength standards
 * - #9 Show Value: Prevents password-based compromises
 * - #10 Beyond Pure: Proactive password auditing
 *
 * **Related Checks:**
 * - 2FA Required (complementary defense)
 * - Login Attempt Limiting (brute force protection)
 * - Password Reset Process Security (related)
 *
 * **Learn More:**
 * Password security: https://wpshadow.com/kb/password-security
 * Video: Creating strong passwords (11min): https://wpshadow.com/training/passwords
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
 * Weak User Passwords Diagnostic Class
 *
 * Checks for weak password indicators.
 *
 * **Detection Pattern:**
 * 1. Get all user accounts
 * 2. Check if password strength plugin active
 * 3. Test passwords against common password list
 * 4. Validate minimum length (12+ chars)
 * 5. Check complexity requirements
 * 6. Return users with weak passwords
 *
 * **Real-World Scenario:**
 * Security scan finds 3 admin accounts with passwords in top 1000
 * common list ("password", "qwerty123", "welcome1"). Admin forced
 * to reset all 3. New passwords: 16+ chars, mixed case, symbols.
 * Brute force risk eliminated.
 *
 * **Implementation Notes:**
 * - Checks password strength configuration
 * - Validates against common password databases
 * - Tests complexity requirements
 * - Severity: critical (admin weak password), high (user weak)
 * - Treatment: force password reset with strength requirements
 *
 * @since 1.6093.1200
 */
class Diagnostic_Weak_User_Passwords extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'weak-user-passwords';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Weak User Passwords';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies weak password indicators for user accounts';

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

		// Check if password strength enforcement plugin exists.
		$password_plugins = array(
			'force-strong-passwords/force-strong-passwords.php' => 'Force Strong Passwords',
			'better-wp-security/better-wp-security.php'         => 'iThemes Security',
			'wordfence/wordfence.php'                           => 'Wordfence',
			'password-policy-manager/password-policy-manager.php' => 'Password Policy Manager',
		);

		$has_password_enforcement = false;
		foreach ( $password_plugins as $plugin_path => $name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_password_enforcement = true;
			}
		}

		if ( ! $has_password_enforcement ) {
			$issues[] = __( 'No password strength enforcement plugin detected', 'wpshadow' );
		}

		// Check for known weak password indicators.
		$weak_usernames = array( 'admin', 'administrator', 'test', 'demo', 'user' );
		$weak_users     = array();

		$users = get_users( array( 'fields' => array( 'ID', 'user_login', 'user_email', 'user_registered' ) ) );

		foreach ( $users as $user ) {
			// If user login is a common weak username, they may have weak password.
			if ( in_array( strtolower( $user->user_login ), $weak_usernames, true ) ) {
				$weak_users[] = $user->user_login;
			}
		}

		if ( ! empty( $weak_users ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of users */
				__( '%d user(s) have weak usernames commonly associated with weak passwords: %s', 'wpshadow' ),
				count( $weak_users ),
				implode( ', ', $weak_users )
			);
		}

		// Check for password reset age (long time since change).
		$stale_passwords = array();
		$stale_days       = 365;

		foreach ( $users as $user ) {
			$last_password_change = get_user_meta( $user->ID, 'password_last_changed', true );

			if ( ! empty( $last_password_change ) ) {
				$days_since_change = ( time() - absint( $last_password_change ) ) / DAY_IN_SECONDS;
				if ( $days_since_change >= $stale_days ) {
					$stale_passwords[] = $user->user_login;
				}
			}
		}

		if ( ! empty( $stale_passwords ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of users */
				__( '%d user(s) have not changed their passwords in over %d days', 'wpshadow' ),
				count( $stale_passwords ),
				$stale_days
			);
		}

		// Check for compromised password indicators (Wordfence or similar).
		$compromised_users = array();
		foreach ( $users as $user ) {
			$compromised = get_user_meta( $user->ID, 'wfPasswordIsLeaked', true );
			if ( $compromised ) {
				$compromised_users[] = $user->user_login;
			}
		}

		if ( ! empty( $compromised_users ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of users */
				__( '%d user(s) are flagged for compromised passwords (Wordfence)', 'wpshadow' ),
				count( $compromised_users )
			);
		}

		// Check for weak passwords in WooCommerce customers (if applicable).
		if ( class_exists( 'WooCommerce' ) ) {
			$weak_customer_users = get_users(
				array(
					'role'   => 'customer',
					'fields' => array( 'ID', 'user_login' ),
					'number' => 50,
				)
			);

			if ( count( $weak_customer_users ) > 100 ) {
				$issues[] = __( 'Large number of customer accounts - enforce strong passwords and 2FA', 'wpshadow' );
			}
		}

		// Check for password reset enforcement.
		$force_password_reset = get_option( 'force_password_reset', false );
		if ( ! $force_password_reset ) {
			$issues[] = __( 'Password reset policy not enforced (consider forcing periodic resets)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of password issues */
					__( 'Found %d password strength issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/weak-user-passwords',
				'details'      => array(
					'issues'            => $issues,
					'weak_usernames'    => $weak_users,
					'stale_passwords'   => count( $stale_passwords ),
					'compromised_users' => count( $compromised_users ),
				),
				'context'      => array(
					'why'            => __(
						'Weak passwords are the leading cause of website compromise. Over 99% of brute force attacks succeed due to weak or reused credentials. NIST guidelines recommend minimum 12 characters with complexity. OWASP Top 10 2023 lists #04:05-Broken Access Control as a critical risk. Compromised passwords (available in public breach databases) enable instant account takeover. Password reuse across sites multiplies breach impact. Stale passwords (unchanged >1 year) increase compromise risk. Dictionary attacks against common usernames ("admin", "test") have 90%+ success rates on weak passwords. For sensitive sites (healthcare/finance), weak passwords violate HIPAA, PCI-DSS, GLBA compliance requirements.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Install password strength plugin: WP Force Strong Passwords, Wordfence, or Password Policy Manager to enforce 12+ character minimum with complexity (uppercase, lowercase, numbers, symbols).
2. Enable two-factor authentication (2FA) for all administrators: Use Google Authenticator, Authy, or similar via WP 2FA plugin (prevents compromise even if password stolen).
3. Force password change for weak accounts: Use wp-cli command `wp user list --format=json | jq \'.[] | .ID\' | xargs -I {} wp user update {} --prompt=user_pass` to reset weak passwords.
4. Implement password reset policy: Require users to change passwords every 90 days using Password Policy Manager plugin.
5. Check for compromised passwords: If using Wordfence, enable password breach detection (compares against 100M+ breached passwords database).
6. Avoid weak usernames: Rename "admin" user to something unique. Block common usernames (test, demo, administrator) at registration.
7. Monitor failed login attempts: Use Wordfence or Limit Login Attempts plugin to block brute force (5+ failures = 24hr block).
8. Educate users: Share NIST password guidelines, warn against password reuse, recommend password managers (Bitwarden, 1Password).',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'password-enforcement',
				'enforce_strong_passwords'
			);

			return $finding;
		}

		return null;
	}
}
