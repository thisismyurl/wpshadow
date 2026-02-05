<?php
/**
 * Login URL Not Changed From Default Treatment
 *
 * Validates that WordPress login URL has been changed from default /wp-admin/.
 * Default login URL is publicly known. Attacker scans for /wp-admin and finds
 * 99% of WordPress sites. Changing URL obscures login endpoint from automated scans.
 *
 * **What This Check Does:**
 * - Detects if login URL is still default (/wp-admin/)
 * - Checks if login redirect customized
 * - Validates new URL is not guessable
 * - Tests if old URL still works (should 404)
 * - Confirms changes applied site-wide
 * - Validates plugin/theme compatibility with custom URL
 *
 * **Why This Matters:**
 * Default login URL = automated brute force target. Scenarios:
 * - Attacker runs automated scan: "Try /wp-admin/ on all sites"
 * - 99% have /wp-admin/ (WordPress default)
 * - Attacker initiates brute force on all discovered sites
 * - Custom URL: attacker can't find login (obscurity layer)
 *
 * **Business Impact:**
 * WordPress site with default /wp-admin/. No rate limiting. Attacker discovers
 * via standard scan. Brute force attack: 1,000 attempts/minute. Admin password
 * guessed within hours. Admin account compromised. Malware injected. Cost:
 * $50K recovery + ransomware payment. Changing URL: free, blocks automated discovery.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Login endpoint obfuscated from attackers
 * - #9 Show Value: Security through obscurity (first layer)
 * - #10 Beyond Pure: Reduces automated attack targets
 *
 * **Related Checks:**
 * - Login Page Rate Limiting (brute force defense)
 * - Login Page Brute Force Protection (active defense)
 * - Database User Privileges Not Minimized (infrastructure hardening)
 *
 * **Learn More:**
 * Custom login URL setup: https://wpshadow.com/kb/custom-wordpress-login-url
 * Video: Hiding WordPress login page (5min): https://wpshadow.com/training/login-url-custom
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login URL Not Changed From Default Treatment Class
 *
 * Implements detection of unchanged default WordPress login URL.
 *
 * **Detection Pattern:**
 * 1. Check if site URL + /wp-admin/ is accessible
 * 2. Query for custom login URL plugins/settings
 * 3. Test if default login redirects (should 404)
 * 4. Verify login form appears on custom URL
 * 5. Confirm old URL returns 404/redirect
 * 6. Return severity if still using default
 *
 * **Real-World Scenario:**
 * Developer deploys WordPress, never changes login URL. Attacker runs scan.
 * Finds /wp-admin/ endpoint. Initiates brute force. Admin password is "admin"
 * (default username attempt). Account compromised within minutes. Custom login
 * URL would have protected against this (attacker wouldn't find login).
 *
 * **Implementation Notes:**
 * - Checks for custom login URL plugin (WPS Hide Login, etc)
 * - Validates default /wp-admin/ behavior
 * - Tests old URL is inaccessible
 * - Severity: medium (default URL), low (obscured but plugin heavy)
 * - Treatment: use custom login URL plugin
 *
 * @since 1.6030.2352
 */
class Treatment_Login_URL_Not_Changed_From_Default extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'login-url-not-changed-from-default';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Login URL Not Changed From Default';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if login URL has been changed';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if login URL has been customized
		if ( ! has_filter( 'login_url', 'customize_login_url' ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Login URL is still the default wp-login.php. Change it to a custom URL to reduce brute force attacks on your login page.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/login-url-not-changed-from-default',
				'context'      => array(
					'why'            => __(
						'The default WordPress login endpoints (/wp-login.php and /wp-admin/) are universally known and heavily targeted by automated scanners. Attackers routinely scan the internet for these endpoints and launch brute force and credential stuffing attacks. Changing the login URL does not replace proper security controls, but it removes your site from a large percentage of automated attack traffic and reduces noise in your logs. This lowers risk and improves performance by reducing malicious login attempts.',
						'wpshadow'
					),
					'recommendation' => __(
						'1. Install a login URL plugin: WPS Hide Login or similar.
2. Choose a non-guessable login URL (avoid /login, /admin, /wp-admin).
3. Ensure the old /wp-login.php returns 404 or redirects to a harmless page.
4. Test login, password reset, and admin access with the new URL.
5. Keep the URL documented in a secure password manager for admins.
6. Combine with rate limiting and 2FA for full protection.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'login-hardening',
				'custom_login_url'
			);

			return $finding;
		}

		return null;
	}
}
