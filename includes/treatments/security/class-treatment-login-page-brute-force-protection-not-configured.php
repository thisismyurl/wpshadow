<?php
/**
 * Login Page Brute Force Protection Not Configured Treatment
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
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Page Brute Force Protection Not Configured Treatment Class
 *
 * Implements detection of missing login rate limiting/brute force protection.\n *
 * **Detection Pattern:**
 * 1. Check for brute force protection plugin/feature\n * 2. Query failed login tracking\n * 3. Validate lockout threshold (after N failed attempts)\n * 4. Check lockout duration\n * 5. Test if CAPTCHA/MFA required\n * 6. Return severity if protection missing\n *
 * **Real-World Scenario:**
 * WordPress site vulnerable to brute force (no plugin protection). Attacker uses\n * common password list (10K passwords). Starts 10 concurrent attack threads.\n * After 1 hour: 100K login attempts sent. Server overloaded (CPU busy validating\n * passwords). Site becomes slow/unusable for legitimate users. With rate limit:\n * 5 failures blocks IP for 15 minutes. Attack defeated in minutes.\n *
 * **Implementation Notes:**
 * - Checks for login rate limiting plugin\n * - Validates failed attempt tracking\n * - Confirms lockout mechanism works\n * - Severity: critical (no protection), high (weak lockout)\n * - Treatment: enable brute force protection plugin\n *
 * @since 1.6093.1200
 */
class Treatment_Login_Page_Brute_Force_Protection_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'login-page-brute-force-protection-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Login Page Brute Force Protection Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if brute force protection is configured';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Login_Page_Brute_Force_Protection_Not_Configured' );
	}
}
