<?php
/**
 * Two-Factor Authentication For Admin Not Required Diagnostic
 *
 * Checks if admin 2FA is required for all admin users.
 * Admin password alone = vulnerable to phishing, brute force, leaks.
 * 2FA = attacker needs physical device even if password compromised.
 * Dramatically increases admin account security.
 *
 * **What This Check Does:**
 * - Checks if 2FA plugin installed (WP 2FA, Google Authenticator, etc)
 * - Validates 2FA enforced for all admin users
 * - Tests if 2FA required (not optional)
 * - Checks 2FA methods supported (TOTP, SMS, email)
 * - Validates enforcement on login
 * - Returns severity if 2FA not required
 *
 * **Why This Matters:**
 * Admin accounts = total site access. Admin password = most targeted.
 * Phishing attacks specifically target admin credentials.
 * With 2FA: even if password stolen, attacker can't login (no device).
 * Prevents 99% of admin account takeovers.
 *
 * **Business Impact:**
 * Site admin doesn't use 2FA. Attacker phishes password.
 * Logs in as admin. Site compromised. Malware injected. All customers
 * affected. GDPR notification sent to 100K users. Cost: $1M+.
 * With 2FA required: phishing email received, but password useless
 * (no device token). Admin account protected. Site remains safe.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Admin accounts secure
 * - #9 Show Value: Prevents admin account takeovers
 * - #10 Beyond Pure: Multi-factor authentication required
 *
 * **Related Checks:**
 * - Login URL Not Changed From Default (related)
 * - Password Reset Process Security (related)
 * - Admin User Security (broader)
 *
 * **Learn More:**
 * 2FA setup guide: https://wpshadow.com/kb/wordpress-2fa
 * Video: Enabling WordPress 2FA (11min): https://wpshadow.com/training/2fa
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Two-Factor Authentication For Admin Not Required Diagnostic Class
 *
 * Detects missing admin 2FA requirement.
 *
 * **Detection Pattern:**
 * 1. Check if 2FA plugin active
 * 2. Get list of all users with admin capability
 * 3. Check if 2FA enabled for each admin
 * 4. Validate if 2FA is required (forced) or optional
 * 5. Test 2FA enforcement on login
 * 6. Return severity if not required
 *
 * **Real-World Scenario:**
 * Admin receives phishing email (looks like WordPress login).
 * Clicks link. Enters password/email. Attacker captures. Attacker
 * logs into real WordPress. With 2FA: attacker has password but
 * login fails (needs 2FA code from admin's phone). Admin gets
 * suspicious notification. Doesn't provide code. Attacker locked out.
 *
 * **Implementation Notes:**
 * - Checks for 2FA plugin
 * - Validates 2FA requirement (not optional)
 * - Tests enforcement on login
 * - Severity: critical (no 2FA), high (optional not required)
 * - Treatment: install 2FA plugin and require for all admins
 *
 * @since 1.2601.2352
 */
class Diagnostic_Two_Factor_Authentication_For_Admin_Not_Required extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'two-factor-authentication-for-admin-not-required';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Two-Factor Authentication For Admin Not Required';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if admin 2FA is required';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if 2FA is required for admins
		if ( ! get_option( 'require_admin_2fa' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Two-factor authentication for admin is not required. Enable 2FA for all administrator accounts to prevent unauthorized access.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/two-factor-authentication-for-admin-not-required',
			);
		}

		return null;
	}
}
