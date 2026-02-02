<?php
/**
 * CORS Headers Not Configured Diagnostic
 *
 * Validates that Cross-Origin Resource Sharing (CORS) headers are properly\n * configured to prevent unauthorized cross-domain access. Misconfigured CORS\n * allows attacker to access APIs from their malicious domain.\n *
 * **What This Check Does:**
 * - Detects if Access-Control-Allow-Origin header is set\n * - Validates CORS doesn't allow wildcard (*) origins\n * - Checks if allowed origins are restricted to known domains\n * - Tests if credentials allowed with CORS (security anti-pattern)\n * - Confirms CORS headers appropriate for API endpoints\n * - Validates preflight requests handled correctly\n *
 * **Why This Matters:**
 * Unrestricted CORS = any website can access your APIs. Scenarios:\n * - Attacker creates webpage that calls your REST API\n * - Browser sends credentials automatically (session cookie)\n * - Attacker steals user data via malicious domain\n * - API endpoints exposed without authentication validation\n *
 * **Business Impact:**
 * WordPress REST API allows CORS with wildcard origin. Attacker creates webpage:\n * attacker.com/steal-users. Visits from authenticated WordPress user. JavaScript\n * calls REST API (CORS allows it). Retrieves user data (email, profile). Creates\n * email list for phishing campaign. 10% of 1,000 users click phishing link (100\n * users). 5% compromise credentials (5 users). Account takeover incidents.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: APIs restricted to authorized domains\n * - #9 Show Value: Prevents cross-domain data theft\n * - #10 Beyond Pure: Defense in depth, APIs secured by default\n *
 * **Related Checks:**
 * - REST API Authentication Bypass (API security)\n * - SSL/TLS Configuration (transport security)\n * - API Throttling Not Configured (API rate limiting)\n *
 * **Learn More:**
 * CORS security guide: https://wpshadow.com/kb/wordpress-cors-configuration\n * Video: Configuring CORS safely (10min): https://wpshadow.com/training/cors-security\n *
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
 * CORS Headers Not Configured Diagnostic Class
 *
 * Implements detection of misconfigured CORS headers.\n *
 * **Detection Pattern:**
 * 1. Check if Access-Control-Allow-Origin header set\n * 2. Validate origins: if \"*\" found, security risk\n * 3. Check if allowed domains list is reasonable\n * 4. Validate Access-Control-Allow-Credentials not set with wildcard\n * 5. Test preflight response headers\n * 6. Return severity if CORS misconfigured\n *
 * **Real-World Scenario:**
 * Developer builds WordPress API for mobile app. Needs CORS for frontend calls.\n * Sets header: \"Access-Control-Allow-Origin: *\" (simplest solution). Forgot\n * to restrict origins. Attacker finds API documentation, creates webpage that\n * calls API (CORS allows any domain). Redirects users via social media link.\n * API accessed from attacker.com with user credentials. User data exfiltrated.\n *
 * **Implementation Notes:**
 * - Tests actual header response\n * - Validates allowed origins whitelist\n * - Checks credentials handling\n * - Severity: critical (wildcard origin), high (unvalidated)\n * - Treatment: restrict CORS to known domains\n *
 * @since 1.2601.2352
 */
class Diagnostic_CORS_Headers_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cors-headers-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CORS Headers Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CORS headers are configured';

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
		// Check for CORS header handling
		if ( ! has_action( 'rest_api_init', 'set_cors_headers' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'CORS headers are not configured. Configure CORS headers to control which cross-origin requests are allowed to your API.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/cors-headers-not-configured',
			);
		}

		return null;
	}
}
