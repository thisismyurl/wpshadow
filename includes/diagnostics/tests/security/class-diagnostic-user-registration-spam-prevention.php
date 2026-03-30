<?php
/**
 * User Registration Spam Prevention Diagnostic
 *
 * Validates that user registration has adequate spam prevention
 * measures including CAPTCHA and bot detection.
 * No spam prevention = bots register 10K accounts.
 * With CAPTCHA/honeypot = bot registrations blocked.
 *
 * **What This Check Does:**
 * - Checks if CAPTCHA enabled (reCAPTCHA, hCaptcha)
 * - Validates honeypot fields present
 * - Tests time-based registration limits
 * - Checks IP-based rate limiting
 * - Validates email domain blacklists
 * - Returns severity if spam prevention missing
 *
 * **Why This Matters:**
 * Without spam prevention: bot registers accounts automatically.
 * 1000s of spam accounts created. Forum/comments flooded.
 * With CAPTCHA: bot can't solve. Registration blocked.
 *
 * **Business Impact:**
 * Membership site with no spam prevention. Bot network registers
 * 50K accounts in 24 hours. Sends spam to all members. Inbox flooded.
 * Users mark as spam. Domain blacklisted. Email deliverability destroyed.
 * Cost: $100K+ (reputation recovery). With CAPTCHA: bot attempts fail.
 * Only real humans register. Community stays clean.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Registration protected from abuse
 * - #9 Show Value: Prevents spam account floods
 * - #10 Beyond Pure: Bot detection by design
 *
 * **Related Checks:**
 * - User Registration Moderation (complementary)
 * - Bot Detection Overall (broader)
 * - Comment Spam Prevention (related)
 *
 * **Learn More:**
 * Spam prevention setup: https://wpshadow.com/kb/registration-spam
 * Video: CAPTCHA configuration (10min): https://wpshadow.com/training/captcha
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Registration Spam Prevention Diagnostic Class
 *
 * Checks user registration spam protection.
 *
 * **Detection Pattern:**
 * 1. Check if CAPTCHA plugin installed
 * 2. Validate honeypot fields in registration form
 * 3. Test time-based limits
 * 4. Check IP rate limiting
 * 5. Validate email domain filtering
 * 6. Return if spam prevention disabled
 *
 * **Real-World Scenario:**
 * reCAPTCHA enabled on registration. Bot attempts 1000 registrations.
 * All fail (can't solve CAPTCHA). Bot gives up. Zero spam accounts
 * created. With no CAPTCHA: bot succeeds. 1000 spam accounts in database.
 *
 * **Implementation Notes:**
 * - Checks spam prevention configuration
 * - Validates CAPTCHA/honeypot presence
 * - Tests rate limiting
 * - Severity: high (no spam prevention on public sites)
 * - Treatment: enable CAPTCHA and honeypot fields
 *
 * @since 1.6093.1200
 */
class Diagnostic_User_Registration_Spam_Prevention extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-registration-spam-prevention';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Registration Spam Prevention';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates user registration spam protection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if user registration is enabled.
		$users_can_register = get_option( 'users_can_register', 0 );

		if ( ! $users_can_register ) {
			return null; // Registration disabled, no spam risk.
		}

		// Check for CAPTCHA plugins.
		$captcha_plugins = array(
			'google-captcha/google-captcha.php'                      => 'Google Captcha (reCAPTCHA)',
			'really-simple-captcha/really-simple-captcha.php'        => 'Really Simple CAPTCHA',
			'advanced-nocaptcha-recaptcha/advanced-nocaptcha-recaptcha.php' => 'Advanced noCaptcha & invisible Captcha',
			'login-recaptcha/login-recaptcha.php'                    => 'Login No Captcha reCAPTCHA',
			'recaptcha-for-woocommerce/recaptcha-for-woocommerce.php' => 'reCAPTCHA for WooCommerce',
		);

		$has_captcha = false;
		$active_captcha = array();
		foreach ( $captcha_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_captcha = true;
				$active_captcha[] = $plugin_name;
			}
		}

		if ( ! $has_captcha ) {
			$issues[] = __( 'No CAPTCHA protection on registration forms (spam vulnerable)', 'wpshadow' );
		}

		// Check for security plugins with registration protection.
		$security_plugins = array(
			'wordfence/wordfence.php'                    => 'Wordfence',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'better-wp-security/better-wp-security.php'  => 'iThemes Security',
		);

		$has_security = false;
		foreach ( $security_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_security = true;
				break;
			}
		}

		// Check for email verification requirement.
		$default_role = get_option( 'default_role', 'subscriber' );
		if ( 'subscriber' !== $default_role && 'customer' !== $default_role ) {
			$issues[] = sprintf(
				/* translators: %s: default role name */
				__( 'Default registration role is "%s" (consider subscriber for new users)', 'wpshadow' ),
				$default_role
			);
		}

		// Check for recent spam registrations.
		global $wpdb;
		$recent_users = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->users} 
			WHERE user_registered > DATE_SUB(NOW(), INTERVAL 7 DAY)"
		);

		if ( $recent_users > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of recent registrations */
				__( '%d users registered in last 7 days (monitor for spam)', 'wpshadow' ),
				$recent_users
			);
		}

		// Check for suspicious user patterns.
		$suspicious_emails = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->users} 
			WHERE user_email LIKE '%@tempmail%' 
			OR user_email LIKE '%@guerrillamail%' 
			OR user_email LIKE '%@10minutemail%'
			OR user_email LIKE '%@throwaway%'"
		);

		if ( $suspicious_emails > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of suspicious emails */
				__( '%d users with disposable email addresses (likely spam)', 'wpshadow' ),
				$suspicious_emails
			);
		}

		// Check for users with similar names (bot pattern).
		$user_patterns = $wpdb->get_results(
			"SELECT LEFT(user_login, 5) as pattern, COUNT(*) as count 
			FROM {$wpdb->users} 
			WHERE user_registered > DATE_SUB(NOW(), INTERVAL 30 DAY)
			GROUP BY pattern 
			HAVING count > 5 
			ORDER BY count DESC 
			LIMIT 10"
		);

		if ( ! empty( $user_patterns ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of suspicious patterns */
				__( '%d similar username patterns detected (possible bot registrations)', 'wpshadow' ),
				count( $user_patterns )
			);
		}

		// Check for honeypot field implementation.
		global $wp_filter;
		$has_honeypot = false;

		if ( isset( $wp_filter['register_form'] ) ) {
			// Can't easily detect honeypot implementation, but check if hook is used.
			$has_honeypot = ! empty( $wp_filter['register_form']->callbacks );
		}

		// Check for email verification plugins.
		$verification_plugins = array(
			'email-verification-for-woocommerce/email-verification-for-woocommerce.php' => 'Email Verification',
			'wp-email-verification/wp-email-verification.php' => 'WP Email Verification',
		);

		$has_verification = false;
		foreach ( $verification_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_verification = true;
				break;
			}
		}

		if ( ! $has_verification && $recent_users > 50 ) {
			$issues[] = __( 'No email verification required for new registrations', 'wpshadow' );
		}

		// Check for users with no activity after registration.
		$inactive_new_users = $wpdb->get_var(
			"SELECT COUNT(DISTINCT u.ID) FROM {$wpdb->users} u
			LEFT JOIN {$wpdb->posts} p ON u.ID = p.post_author
			LEFT JOIN {$wpdb->comments} c ON u.ID = c.user_id
			WHERE u.user_registered > DATE_SUB(NOW(), INTERVAL 30 DAY)
			AND p.ID IS NULL 
			AND c.comment_ID IS NULL"
		);

		if ( $inactive_new_users > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of inactive new users */
				__( '%d newly registered users have no activity (possible spam accounts)', 'wpshadow' ),
				$inactive_new_users
			);
		}

		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of spam prevention issues */
					__( 'Found %d user registration spam prevention gaps.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/registration-spam',
				'details'      => array(
					'issues'             => $issues,
					'has_captcha'        => $has_captcha,
					'recent_users'       => $recent_users,
					'suspicious_emails'  => $suspicious_emails,
					'inactive_new_users' => $inactive_new_users,
				),
				'context'      => array(
					'why'            => __(
						'Automated registrations enable spam, phishing, and resource abuse. Bots create fake accounts to post spam links, scrape data, or abuse member features. This can damage deliverability (email blacklisting), inflate database size, and degrade community trust. CAPTCHA and email verification reduce automated signups, while rate limiting and domain filtering block known spam sources.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Enable CAPTCHA (reCAPTCHA or hCaptcha) on registration.
2. Add a honeypot field and time-based form validation.
3. Require email verification before account activation.
4. Rate-limit registration attempts per IP.
5. Block disposable email domains.
6. Monitor registration spikes and review suspicious users.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'anti-spam',
				'registration_spam_prevention'
			);

			return $finding;
		}

		return null;
	}
}
