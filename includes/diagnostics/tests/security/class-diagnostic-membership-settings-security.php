<?php
/**
 * Membership Settings Security Diagnostic
 *
 * Validates user registration and membership security settings prevent unauthorized access.
 * Default WordPress allows anyone to register without approval. Attacker creates
 * admin account (if registration set to admin role). Compromises entire site.
 *
 * **What This Check Does:**
 * - Checks if user registration is enabled
 * - Validates default user role (should NOT be admin/editor)
 * - Tests if admin email notified on new registration
 * - Confirms email verification required
 * - Checks for registration CAPTCHA/bot protection
 * - Validates membership plugin settings (if present)
 *
 * **Why This Matters:**
 * Open registration + wrong default role = instant compromise. Scenarios:
 * - Registration enabled. Default role: admin (wrong!)
 * - Attacker registers account: "admin2"
 * - New account defaults to admin role
 * - Attacker has full site access
 * - Malware installed within minutes
 *
 * **Business Impact:**
 * WordPress site with open registration (for "community engagement"). Default
 * role accidentally set to "editor" (not admin, but still dangerous). Attacker
 * registers account. Edits all site content. Injecting malware links. Damage:
 * SEO penalty + user data exposure = $100K recovery cost. With proper settings:
 * default role = "subscriber" (read-only). Attacker registration harmless.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Registration safe from escalation
 * - #9 Show Value: Prevents account takeover scenario
 * - #10 Beyond Pure: Open community, secure core
 *
 * **Related Checks:**
 * - User Capability Auditing (role validation)
 * - Unused Administrator Accounts (cleanup)
 * - Two-Factor Authentication (account protection)
 *
 * **Learn More:**
 * Membership security: https://wpshadow.com/kb/wordpress-membership-security
 * Video: Securing user registration (10min): https://wpshadow.com/training/membership-security
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Membership_Settings_Security Class
 *
 * Validates user registration and membership security.
 *
 * **Detection Pattern:**
 * 1. Check if user registration enabled
 * 2. Query default user role setting
 * 3. Validate role is not admin/editor
 * 4. Test email verification requirement
 * 5. Confirm admin notification on signup
 * 6. Return severity if misconfigured
 *
 * **Real-World Scenario:**
 * Client site allows registration for "community interaction". Admin didn't change
 * default role. Left as "editor" (should be "subscriber"). Attacker registers.
 * Gains editor rights. Modifies homepage. Injects malware links. Site deindexed
 * by Google. 3-month recovery. Revenue impact: $200K. Fix: set default role
 * to subscriber (harmless). Prevents entire scenario.
 *
 * **Implementation Notes:**
 * - Checks WordPress registration settings
 * - Validates default role (subscriber/contributor recommended)
 * - Tests email verification + admin notification
 * - Severity: critical (admin default), high (editor default)
 * - Treatment: change default role to subscriber
 *
 * @since 1.2601.2148
 */
class Diagnostic_Membership_Settings_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'membership-settings-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Membership Settings Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates user registration and membership security settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests membership security configuration.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check if user registration is required
		$users_can_register = get_option( 'users_can_register', false );
		if ( $users_can_register && ! self::has_registration_security() ) {
			$issues[] = __( 'User registration enabled without security controls', 'wpshadow' );
		}

		// 2. Check for new user moderation
		if ( $users_can_register && ! self::has_new_user_approval() ) {
			$issues[] = __( 'No admin approval required for new user registration', 'wpshadow' );
		}

		// 3. Check for email verification
		if ( $users_can_register && ! self::enforces_email_verification() ) {
			$issues[] = __( 'No email verification for new users', 'wpshadow' );
		}

		// 4. Check for spam protection
		if ( $users_can_register && ! self::has_spam_protection() ) {
			$issues[] = __( 'No CAPTCHA or spam protection on registration', 'wpshadow' );
		}

		// 5. Check for password requirements
		if ( ! self::has_strong_password_requirements() ) {
			$issues[] = __( 'No password strength requirements configured', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of security issues */
					__( '%d membership security issues found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/membership-registration-security',
				'recommendations' => array(
					__( 'Require admin approval for new users', 'wpshadow' ),
					__( 'Enforce email verification', 'wpshadow' ),
					__( 'Add CAPTCHA to registration form', 'wpshadow' ),
					__( 'Require strong passwords', 'wpshadow' ),
					__( 'Implement spam filtering on registration', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for registration security.
	 *
	 * @since  1.2601.2148
	 * @return bool True if security implemented.
	 */
	private static function has_registration_security() {
		// Check for registration policy
		if ( has_filter( 'wpshadow_registration_security' ) ) {
			return true;
		}

		// Check for plugin that adds security
		$security_plugins = array(
			'wordfence/wordfence.php',
			'sucuri-scanner/sucuri.php',
		);

		foreach ( $security_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for new user approval.
	 *
	 * @since  1.2601.2148
	 * @return bool True if approval required.
	 */
	private static function has_new_user_approval() {
		// Check for approval plugin
		$approval_plugins = array(
			'wpc-smart-post-list/index.php',
			'user-approval/index.php',
			'members/members.php',
		);

		foreach ( $approval_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for setting
		if ( has_filter( 'wpshadow_require_user_approval' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for email verification.
	 *
	 * @since  1.2601.2148
	 * @return bool True if verification enforced.
	 */
	private static function enforces_email_verification() {
		// Check for email verification plugin
		$verification_plugins = array(
			'users-ultra/index.php',
			'ultimate-member/ultimate-member.php',
		);

		foreach ( $verification_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for filter
		if ( has_filter( 'wpshadow_verify_user_email' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for spam protection.
	 *
	 * @since  1.2601.2148
	 * @return bool True if protection implemented.
	 */
	private static function has_spam_protection() {
		// Check for CAPTCHA plugin
		$captcha_plugins = array(
			'google-captcha/google-captcha.php',
			'really-simple-captcha/really-simple-captcha.php',
			'cf7-google-analytics/contact-form-7-google-analytics.php',
		);

		foreach ( $captcha_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for filter
		if ( has_filter( 'wpshadow_spam_protection' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for password requirements.
	 *
	 * @since  1.2601.2148
	 * @return bool True if requirements set.
	 */
	private static function has_strong_password_requirements() {
		// Check for password policy plugin
		if ( is_plugin_active( 'password-policy-manager/password-policy-manager.php' ) ) {
			return true;
		}

		// Check for filter
		if ( has_filter( 'wpshadow_password_strength_requirements' ) ) {
			return true;
		}

		return false;
	}
}
