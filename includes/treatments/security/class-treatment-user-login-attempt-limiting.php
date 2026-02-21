<?php
/**
 * User Login Attempt Limiting Treatment
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
 * @subpackage Treatments
 * @since      1.6032.1340
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Login Attempt Limiting Treatment Class
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
 * @since 1.6032.1340
 */
class Treatment_User_Login_Attempt_Limiting extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-login-attempt-limiting';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'User Login Attempt Limiting';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates login attempt limiting for brute force protection';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_User_Login_Attempt_Limiting' );
	}
}
