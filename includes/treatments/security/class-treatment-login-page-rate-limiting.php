<?php
/**
 * Login Page Rate Limiting Treatment
 *
 * Checks if login page has rate limiting protection against brute force and DoS\n * attacks. Rate limiting prevents attackers from attempting unlimited password\n * guesses. Without limits: attacker tries 1 million passwords in hours.\n *
 * **What This Check Does:**
 * - Detects if rate limiting implemented on login attempts\n * - Validates limit threshold (N attempts per minute/IP)\n * - Tests if lockout duration appropriate (prevents DoS)\n * - Checks if limit applies per IP, per username, or both\n * - Confirms legitimate users not locked out\n * - Validates monitoring/alerts on rate limit triggers\n *
 * **Why This Matters:**
 * Unlimited login attempts = password guessing succeeds. Scenarios:\n * - No rate limiting on /wp-admin/\n * - Attacker attempts 1,000 passwords/minute\n * - Common password guessed within hours\n * - Account compromise\n * - Full site compromise via admin account\n *
 * **Business Impact:**
 * SaaS platform. Rate limiting set too high (100 attempts/minute). Attacker\n * performs 1M attempts in ~3 hours. Weak password guessed. Admin account stolen.\n * Attacker modifies customer data. 50 customers discover account tampering.\n * Regulatory notification required. Fine: $100K-$500K (GDPR/CCPA). With better\n * rate limiting (5 attempts/minute): attack blocked in seconds.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Login attempts protected\n * - #9 Show Value: Quantified brute force resistance\n * - #10 Beyond Pure: Respects legitimate user attempts\n *
 * **Related Checks:**
 * - Login Page Brute Force Protection (same protection, different name)\n * - API Throttling Not Configured (general rate limiting)\n * - Geolocation Blocking Not Configured (source restriction)\n *
 * **Learn More:**
 * Rate limiting best practices: https://wpshadow.com/kb/wordpress-rate-limiting
 * Video: Implementing rate limiting (9min): https://wpshadow.com/training/rate-limiting\n *
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
 * Login Page Rate Limiting Treatment Class
 *
 * Validates login page rate limiting configuration.\n *
 * **Detection Pattern:**
 * 1. Check for rate limiting plugin/feature\n * 2. Query limit thresholds (attempts per minute)\n * 3. Validate limit tracking (per IP vs per user)\n * 4. Check lockout duration\n * 5. Confirm limits don't cause false positives\n * 6. Return severity if rate limiting missing\n *
 * **Real-World Scenario:**
 * Blog with no rate limiting. Attacker starts password attack. Tries 10 passwords\n * per second (HTTP/2 concurrent requests). After 100 seconds: 1,000 attempts.\n * Admin password guessed within 1 hour. Attacker posts malware links. Site\n * blacklisted. With rate limiting (5 attempts/minute): 5th attempt triggers\n * lockout. Attack blocked immediately.\n *
 * **Implementation Notes:**
 * - Checks for rate limiting on /wp-login.php\n * - Validates limit thresholds\n * - Tests lockout mechanism\n * - Severity: critical (no limits), high (too generous)\n * - Treatment: implement rate limiting (5 failures = 15 min lockout)\n *
 * @since 1.6030.2240
 */
class Treatment_Login_Page_Rate_Limiting extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'login-page-rate-limiting';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Login Page Rate Limiting';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if login page has rate limiting protection against brute force attacks';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Login_Page_Rate_Limiting' );
	}
}
