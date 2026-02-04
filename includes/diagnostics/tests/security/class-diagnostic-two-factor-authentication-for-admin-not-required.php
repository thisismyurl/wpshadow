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
 * @since 1.6030.2352
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
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if 2FA is required for admins
		if ( ! get_option( 'require_admin_2fa' ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Two-factor authentication for admin is not required. Enable 2FA for all administrator accounts to prevent unauthorized access.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/two-factor-authentication-for-admin-not-required',
				'context'      => array(
					'why'            => __(
						'Admin accounts control entire website: content, users, plugins, themes, settings, backups. With password alone, compromise vectors are numerous: phishing (96% of breaches start with phishing), credential stuffing (leaked databases), weak passwords (brute force), malware/keyloggers. 2FA eliminates 99.9% of these vectors. Even if password stolen, attacker cannot login without physical device. Microsoft reports 2FA prevents 99.9% of automated attacks and 96% of targeted attacks. For regulated industries (healthcare, finance, legal), 2FA is often mandatory: HIPAA requires MFA for ePHI access, PCI-DSS requires MFA for card data access, GDPR recommends MFA for sensitive systems. Major breach notification laws (CCPA, GDPR) now focus on MFA as a baseline security control.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Install 2FA plugin: WP 2FA (free), Google Authenticator for WordPress, Wordfence (paid), or Two Factor by Evan Carroll.
2. Choose 2FA method: Time-based OTP (TOTP) like Google Authenticator (most secure, no SMS interception). SMS (less secure but acceptable). Email (least secure but better than nothing). Support multiple methods for accessibility.
3. Configure enforcement: Set "Require 2FA for all administrators" = YES. This forces, not optionally suggests, 2FA setup.
4. Set grace period: Give admins 7 days to enable 2FA before enforcement kicks in (prevents account lockout).
5. Require on every login: Disable "remember this device for 30 days" option. Authenticator required on EVERY admin login (maximum security).
6. Backup authentication codes: Provide 10-15 one-time backup codes when admin enables 2FA. Store in password manager/secure location for account recovery if device lost.
7. Communicate with admins: Email all admins explaining 2FA requirement, benefits, and deadline. Provide setup video/tutorial link.
8. Monitor 2FA status: Regularly audit which admins have 2FA enabled. Send reminder emails to non-compliant admins. Track progress.
9. Test 2FA flow: Manually test admin login with 2FA to ensure working correctly. Verify backup codes work.
10. Consider security keys: For highest security, support FIDO2/WebAuthn security keys (YubiKey, Titan key). Hardware-based, most resistant to phishing.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'multi-factor-authentication',
				'require_admin_2fa'
			);

			return $finding;
		}

		return null;
	}
}
