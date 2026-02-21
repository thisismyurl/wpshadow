<?php
/**
 * Password Reset Process Security Treatment
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
 * @subpackage Treatments
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Password Reset Process Security Treatment
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
class Treatment_Password_Reset_Process_Security extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'password-reset-process-security';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Password Reset Process Security';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates password reset process security measures';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Password_Reset_Process_Security' );
	}
}
