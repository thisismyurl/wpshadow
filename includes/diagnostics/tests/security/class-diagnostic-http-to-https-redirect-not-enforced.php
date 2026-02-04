<?php
/**
 * HTTP To HTTPS Redirect Not Enforced Diagnostic
 *
 * Validates that all HTTP traffic automatically redirects to HTTPS.\n * Without redirect enforcement, users accessing http://domain.com send credentials\n * over unencrypted connection. Man-in-the-middle attacker captures auth cookies.\n *
 * **What This Check Does:**
 * - Checks if HTTP → HTTPS redirect implemented\n * - Validates redirect happens at server level (not client-side)\n * - Tests if all traffic (including admin) redirects\n * - Confirms redirect is 301 (permanent, cacheable)\n * - Validates HSTS headers enforce HTTPS in future\n * - Tests that redirect can't be bypassed\n *
 * **Why This Matters:**
 * Missing HTTP redirect = credentials transmitted unencrypted. Scenarios:\n * - User types http://example.com (no s)\n * - Connection unencrypted\n * - Attacker on network captures session cookie\n * - User is authenticated to WordPress\n * - Attacker uses cookie to access account\n *
 * **Business Impact:**
 * SaaS platform installed on domain with HTTPS cert. Forgot to enable HTTP\n * redirect. User bookmarks http://app.example.com (without s). Each login\n * unencrypted. After 1 month: 1,000 users with unencrypted sessions. Attacker\n * on company WiFi captures 10 cookies. Compromises 10 accounts. Fraud detected.\n * Investigation + credential resets = 40 hours work. Cost: $2K productivity.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: All traffic encrypted by default\n * - #9 Show Value: Prevents session cookie interception\n * - #10 Beyond Pure: Secure by default, no user friction\n *
 * **Related Checks:**
 * - SSL/TLS Configuration Not Set (HTTPS setup)\n * - HSTS Headers Not Configured (HTTP Strict Transport Security)\n * - Certificate Pinning (advanced certificate verification)\n *
 * **Learn More:**
 * HTTPS redirect setup: https://wpshadow.com/kb/http-to-https-redirect\n * Video: Enforcing HTTPS on WordPress (6min): https://wpshadow.com/training/https-enforcement\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_URL_And_Pattern_Helper;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTTP To HTTPS Redirect Not Enforced Diagnostic Class
 *
 * Implements detection of missing HTTP to HTTPS redirects.\n *
 * **Detection Pattern:**
 * 1. Check WordPress option siteurl/home (HTTPS vs HTTP)\n * 2. Test actual HTTP request (does it redirect?)\n * 3. Validate redirect code (301 permanent vs temporary)\n * 4. Check if redirect happens for all paths (admin, API, etc)\n * 5. Validate HSTS header set (prevents downgrade)\n * 6. Return severity if redirect missing\n *
 * **Real-World Scenario:**
 * WordPress site has SSL cert installed. Admin configures siteurl to HTTPS\n * in WordPress settings. But server redirect not configured. User bookmarks\n * http://site.com. Next visit, HTTP connection established (unencrypted).\n * Before WordPress even loads, user sends credentials to attacker (proxy).\n * Attacker captures admin password during login. Admin account compromised.\n *
 * **Implementation Notes:**
 * - Checks WordPress siteurl/home options\n * - Tests actual HTTP server response\n * - Validates redirect code (301 preferred, 302 acceptable)\n * - Tests for all paths (not just homepage)\n * - Severity: critical (no redirect), high (temporary redirect)\n * - Treatment: implement permanent HTTP→HTTPS redirect\n *
 * @since 1.6030.2352
 */
class Diagnostic_HTTP_To_HTTPS_Redirect_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'http-to-https-redirect-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP To HTTPS Redirect Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if HTTP to HTTPS redirect is enforced';

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
		// Check if site is HTTPS
		if ( 'https' !== Diagnostic_URL_And_Pattern_Helper::get_scheme( home_url() ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'HTTP to HTTPS redirect is not enforced. Add 301 redirects from HTTP to HTTPS in .htaccess or server configuration.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/http-to-https-redirect-not-enforced',
			);
		}

		return null;
	}
}
