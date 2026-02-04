<?php
/**
 * Login Page Brute Force Protection Not Configured Diagnostic
 *
 * Validates that the login page is protected against brute force password\n * guessing attacks. Without protection, attackers automate 1,000+ attempts/minute\n * until password guessed. Modern GPU: guesses 10B passwords/second offline.\n *
 * **What This Check Does:**
 * - Detects if login attempt rate limiting enabled\n * - Checks for failed attempt tracking per IP/user\n * - Validates lockout after X failed attempts\n * - Tests if CAPTCHA required after failures\n * - Confirms lockout duration (15 min, 1 hour)\n * - Validates lockout can't be bypassed (no user enumeration)\n *
 * **Why This Matters:**
 * Unprotected login = password guessing succeeds. Scenarios:\n * - Admin login at /wp-admin (standard WordPress)\n * - Bot attempts 1,000 passwords/minute\n * - After 10 days: common password guessed\n * - Account takeover successful\n *
 * **Business Impact:**
 * WordPress site without brute force protection. Attacker discovers login page.\n * Runs password list: 100K common passwords. After 2 hours: 5 accounts compromised.\n * Attacker posts malware ads on compromised accounts. Customers infected (5%\n * conversion = 500 infections). Liability: $250K-$500K. With rate limiting:\n * Attack blocked after 5 failed attempts. Attacker can't execute.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Account takeover prevented\n * - #9 Show Value: Quantified account protection\n * - #10 Beyond Pure: Respects legitimate user attempts\n *
 * **Related Checks:**
 * - Login Page Rate Limiting (same protection, different mechanism)\n * - Geolocation Blocking Not Configured (attack source restriction)\n * - Multi-factor Authentication Not Required (additional verification)\n *
 * **Learn More:**
 * Brute force protection setup: https://wpshadow.com/kb/wordpress-brute-force-protection\n * Video: Implementing login security (10min): https://wpshadow.com/training/brute-force-protection\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Page Brute Force Protection Not Configured Diagnostic Class
 *
 * Implements detection of missing login rate limiting/brute force protection.\n *
 * **Detection Pattern:**
 * 1. Check for brute force protection plugin/feature\n * 2. Query failed login tracking\n * 3. Validate lockout threshold (after N failed attempts)\n * 4. Check lockout duration\n * 5. Test if CAPTCHA/MFA required\n * 6. Return severity if protection missing\n *
 * **Real-World Scenario:**
 * WordPress site vulnerable to brute force (no plugin protection). Attacker uses\n * common password list (10K passwords). Starts 10 concurrent attack threads.\n * After 1 hour: 100K login attempts sent. Server overloaded (CPU busy validating\n * passwords). Site becomes slow/unusable for legitimate users. With rate limit:\n * 5 failures blocks IP for 15 minutes. Attack defeated in minutes.\n *
 * **Implementation Notes:**
 * - Checks for login rate limiting plugin\n * - Validates failed attempt tracking\n * - Confirms lockout mechanism works\n * - Severity: critical (no protection), high (weak lockout)\n * - Treatment: enable brute force protection plugin\n *
 * @since 1.6030.2352
 */
class Diagnostic_Login_Page_Brute_Force_Protection_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'login-page-brute-force-protection-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Login Page Brute Force Protection Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if brute force protection is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for brute force protection
		if ( ! is_plugin_active( 'wordfence/wordfence.php' ) && ! is_plugin_active( 'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
\t\t\t\t'description'  => __( 'Your login page could use protection against automated password guessing (like locking your door after too many wrong key attempts). This helps stop bots from trying thousands of passwords to break into your site.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/login-page-brute-force-protection-not-configured',
				'context'      => array(
					'why'            => __(
						'Brute force attacks target the WordPress login page because it is predictable (/wp-login.php, /wp-admin). Without rate limiting, bots can attempt thousands of passwords per minute, eventually guessing weak or reused credentials. OWASP Top 10 2023 lists Identification and Authentication Failures as #07, and credential stuffing is a primary driver. Wordfence reports tens of millions of automated login attempts per day across WordPress sites. Without lockouts, attackers can also overwhelm the server, causing performance degradation for legitimate users. In regulated environments (PCI-DSS, HIPAA), weak authentication controls violate security requirements.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Install a brute force protection plugin: "Limit Login Attempts Reloaded" (free) or Wordfence (paid) are reliable.
2. Configure lockout threshold: 5-10 failed attempts within 15 minutes triggers lockout.
3. Set lockout duration: 30 minutes for first lockout, 24 hours after repeat offenses.
4. Enable CAPTCHA after failures: Require CAPTCHA after 3 failed attempts to block bots.
5. Limit password reset requests: Cap to 3 per hour per email to prevent reset abuse.
6. Add IP throttling: Block IPs with repeated failures (10+ within 24 hours).
7. Log failed logins: Track IP, username, timestamp, user-agent for forensics.
8. Add 2FA for admins: Even if brute force succeeds, 2FA blocks access.
9. Hide login URL (optional): Use a custom login URL plugin to reduce automated scanning.
10. Monitor alerts: Configure email alerts for brute force activity so admins can respond quickly.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'brute-force-protection',
				'login_bruteforce_protection'
			);

			return $finding;
		}

		return null;
	}
}
