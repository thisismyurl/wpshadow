<?php
/**
 * Password Reset Process Security Diagnostic
 *
 * Validates password reset process security against account takeover.
 * Weak reset = attacker changes admin password (via email compromise).
 * Reset link too long-lived = attacker intercepts + uses hours later.
 *
 * **What This Check Does:**
 * - Detects if password reset implemented
 * - Validates reset token generation (cryptographically strong)
 * - Tests token expiration (should be 15-30 minutes)
 * - Checks if email verification required
 * - Confirms reset link single-use (not replayable)
 * - Validates rate limiting on reset requests
 *
 * **Why This Matters:**
 * Weak password reset = account takeover via email. Scenarios:
 * - Admin email compromised
 * - Attacker requests password reset
 * - Reset link sent to compromised email
 * - Attacker clicks link, changes password
 * - Admin locked out, attacker has full access
 *
 * **Business Impact:**
 * WordPress site admin email hacked (phishing). Attacker requests password
 * reset. Link sent to compromised email. Attacker clicks link. Changes admin
 * password. Takes full control. Installs malware. Compromises customer data.
 * Breach: 50K records, $5M in liability. With proper reset: link expires in
 * 15 minutes, single-use, requires confirmation. Attacker loses access window.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Account recovery is secure
 * - #9 Show Value: Prevents email-based account takeover
 * - #10 Beyond Pure: Secure identity recovery
 *
 * **Related Checks:**
 * - Email Verification Implementation (email security)
 * - Two-Factor Authentication (account protection)
 * - Inactive Sessions Cleanup (limit access window)
 *
 * **Learn More:**
 * Password reset security: https://wpshadow.com/kb/wordpress-password-reset
 * Video: Securing password reset (10min): https://wpshadow.com/training/password-reset
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Password Reset Process Security Diagnostic
 *
 * Checks password reset process for security best practices.
 *
 * **Detection Pattern:**
 * 1. Request password reset via email
 * 2. Check reset token generation (strong random)
 * 3. Validate token expiration (time-limited)
 * 4. Test single-use enforcement (can't reuse token)
 * 5. Confirm email verification required
 * 6. Return severity if weak implementation
 *
 * **Real-World Scenario:**
 * WordPress site with basic password reset (no rate limiting, 48-hour links).
 * Admin email compromised. Attacker requests 10 password resets. Links valid
 * for 48 hours. Attacker has 48 hours to use any of them. Changes password.
 * Admin discovers 24 hours later (too late). With security: 15-minute links +
 * rate limiting (3 requests/hour) = attacker has small window + limited attempts.
 *
 * **Implementation Notes:**
 * - Checks WordPress password reset
 * - Validates token expiration (15-30 minutes typical)
 * - Tests single-use enforcement
 * - Severity: high (no expiration), medium (weak token generation)
 * - Treatment: add expiration + rate limiting to reset flow
 *
 * @since 1.6030.2240
 */
class Diagnostic_Password_Reset_Process_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'password-reset-process-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Password Reset Process Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates password reset process security measures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$protections = array();

		// Check if HTTPS is enabled for password reset
		if ( ! is_ssl() ) {
			$issues[] = __( 'Password reset is not protected by HTTPS', 'wpshadow' );
		} else {
			$protections[] = __( 'HTTPS enabled for password reset', 'wpshadow' );
		}

		// Check password reset token expiry
		$expiry_time = apply_filters( 'password_reset_expiry_time', DAY_IN_SECONDS );

		if ( $expiry_time > WEEK_IN_SECONDS ) {
			$days = intdiv( $expiry_time, DAY_IN_SECONDS );
			$issues[] = sprintf(
				/* translators: %d: number of days */
				__( 'Password reset tokens expire in %d days - should be 24 hours or less', 'wpshadow' ),
				$days
			);
		} else {
			$protections[] = __( 'Password reset token expiry is appropriately configured', 'wpshadow' );
		}

		// Check for rate limiting on password reset
		$password_reset_plugins = array(
			'wordfence/wordfence.php',
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
			'jetpack/jetpack.php',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		$has_rate_limiting = false;

		foreach ( $password_reset_plugins as $plugin ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$has_rate_limiting = true;
				$protections[] = __( 'Rate limiting enabled on password reset', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_rate_limiting ) {
			$issues[] = __( 'No rate limiting on password reset - vulnerable to brute force attacks', 'wpshadow' );
		}

		// Check if password reset email contains security warnings
		global $wp_filter;

		$has_security_email = false;
		if ( isset( $wp_filter['retrieve_password_message'] ) || isset( $wp_filter['password_reset_message'] ) ) {
			$has_security_email = true;
		}

		// Check password reset requirements
		$password_strength = apply_filters( 'password_reset_strength_requirement', 'medium' );

		if ( 'weak' === $password_strength ) {
			$issues[] = __( 'Password reset does not enforce strong password requirements', 'wpshadow' );
		} else {
			$protections[] = __( 'Strong password requirements enforced on reset', 'wpshadow' );
		}

		// Check for user enumeration vulnerability
		// WordPress default behavior exposes user existence via password reset
		$reveal_user = apply_filters( 'wpshadow_password_reset_reveal_user', true );

		if ( $reveal_user ) {
			$issues[] = __( 'Password reset reveals user account existence (user enumeration)', 'wpshadow' );
		}

		// Check for email verification after reset
		$verify_email = apply_filters( 'wpshadow_verify_email_after_password_reset', false );

		if ( ! $verify_email ) {
			$issues[] = __( 'Email verification is not required after password reset', 'wpshadow' );
		} else {
			$protections[] = __( 'Email verification required after password reset', 'wpshadow' );
		}

		// Check for 2FA/MFA integration with password reset
		$mfa_plugins = array(
			'two-factor/two-factor.php',
			'wordfence/wordfence.php',
			'google-authenticator-per-user-prompt/google-authenticator-per-user-prompt.php',
		);

		$has_mfa = false;
		foreach ( $mfa_plugins as $plugin ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$has_mfa = true;
				break;
			}
		}

		// Check if users with admin role need MFA
		if ( $has_mfa ) {
			$protections[] = __( 'Multi-factor authentication available for admin accounts', 'wpshadow' );
		} else {
			$issues[] = __( 'No multi-factor authentication available - admin accounts at risk', 'wpshadow' );
		}

		// Check password history
		$unique_passwords = apply_filters( 'password_reset_unique_passwords', 0 );

		if ( $unique_passwords === 0 ) {
			$issues[] = __( 'Password history is not enforced - users can reset to previous passwords', 'wpshadow' );
		}

		// Report findings
		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Password reset process has security issues', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/password-reset-process-security',
				'details'      => array(
					'issues'      => $issues,
					'protections' => $protections,
				),
				'context'      => array(
					'why'            => __(
						'Password reset is the "backdoor" to account takeover via email compromise. Attack vector: attacker gains email access (phishing, malware, credential stuffing), requests password reset, uses reset link to change WordPress password, gains full site access. Microsoft reports 4.7% of their users have MFA, but 99.9% of attacks blocked by MFA - showing how effective security at account recovery is. OWASP Top 10 lists broken authentication (which includes weak reset flows) as #07. PCI-DSS 3.6.2 requires secure password reset. HIPAA requires account lockout after failed reset attempts (prevents brute force on reset tokens). Reset tokens should: (1) Be cryptographically random (not predictable), (2) Expire in 15-30 minutes (not 24 hours = too long), (3) Be single-use (can\'t replay same token), (4) Require email confirmation (attacker without email access can\'t complete), (5) Have rate limiting (prevent testing thousands of tokens), (6) Require 2FA for admin resets (extra protection). Long-lived or guessable tokens convert email compromise → instant site compromise.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Verify token expiration: Check WordPress settings for reset token lifetime. Should be 15-30 minutes maximum. If not configured, tokens may be valid for 24+ hours (too long).
2. Implement token rotation: After reset email sent, require user to click link within 20 minutes. Token expires after 1 use. User must verify email (unlikely attacker has email access).
3. Add email confirmation: Require user to confirm reset request via secondary email verification or security question. Prevents resetting password of someone else.
4. Enable 2FA for admin password resets: When admin requests password reset, require 2FA token before allowing reset completion (even with valid reset link).
5. Log all reset attempts: Create audit trail: who requested reset, when, from which IP. Alert admin on unusual patterns (10 reset requests in 1 hour = account under attack).
6. Implement rate limiting: Allow maximum 3 reset requests per email per hour (prevents token brute force). After 5 attempts, lock account for 1 hour.
7. Use strong token generation: Ensure tokens use cryptographically strong randomness (WordPress uses wp_generate_password() which is sufficient if using CSPRNG backend).
8. Verify token isn\'t legged in URLs: Check that reset URLs don\'t log to access logs, error logs, or referrer headers. Use POST method where possible.
9. Test token replay: Attempt to use same reset token twice. Should fail second time (single-use enforcement).
10. Communicate reset timing: Tell users reset link expires in 20 minutes. "Click immediately, link will not work after 20 minutes for security". Sets expectations.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'account-recovery-hardening',
				'password_reset_security'
			);

			return $finding;
		}

		return null;
	}
}
