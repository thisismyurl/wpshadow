<?php
/**
 * Two-Factor Authentication Status Treatment
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
 * Two-Factor Authentication Status Treatment Class
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
class Treatment_Two_Factor_Authentication_Status extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'two-factor-authentication-status';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Two-Factor Authentication Status';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates 2FA configuration for admin accounts';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Two_Factor_Authentication_Status' );
	}
}
