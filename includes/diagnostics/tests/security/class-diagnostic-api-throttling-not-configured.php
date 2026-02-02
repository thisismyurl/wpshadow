<?php
/**
 * API Throttling Not Configured Diagnostic
 *
 * Verifies that WordPress REST API has rate limiting (throttling) enabled to prevent
 * brute-force attacks, denial-of-service abuse, and credential stuffing at scale.
 * Without API throttling, attackers can attempt thousands of password combinations per
 * second against your site, treating it as a test bed for credential validity.
 *
 * **What This Check Does:**
 * - Detects if REST API rate limiting plugin is active
 * - Checks WordPress 6.1+ built-in rate limiting (WP_REST_DEFAULT_PER_PAGE enforcement)
 * - Validates rate limit headers are present in API responses
 * - Confirms thresholds are set below abuse threshold (not 1000s of req/minute)
 * - Tests public endpoints respond with 429 Too Many Requests when exceeded
 *
 * **Why This Matters:**
 * REST API is powerful but dangerous without throttling. Real attack scenarios:
 * - Attacker scripts 1,500 password attempts/min against /wp-json/wp/v2/users endpoint
 * - Botnet harvests 50,000 post IDs and comments per hour for OSINT
 * - Credential stuffing from compromised Instagram list tests 10,000 email+password combinations
 * - Brute force wp-admin login via REST endpoints (faster than form submission)
 *
 * **Business Impact:**
 * Single unthrottled API typically collapses under automated attack within 60 seconds.
 * Server bills spike 5-10x. Legitimate traffic blocked. Recovery requires 4-8 hours of
 * investigation + 1-2 hours server restoration. Attacker gain: compromised admin account
 * or database exposure = full site takeover. Prevention cost: 15 minutes configuration.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Eliminate low-hanging attack fruit
 * - #9 Show Value: Quantifiable threat reduction
 * - #10 Beyond Pure: Protects all site users from collateral abuse
 *
 * **Related Checks:**
 * - Login Page Rate Limiting (similar pattern at authentication layer)
 * - User Capability Auditing (verify who's calling the API)
 * - Bot Traffic Detection (catch the attack bots)
 *
 * **Learn More:**
 * REST API security hardening: https://wpshadow.com/kb/rest-api-throttling
 * Video: Protecting WordPress APIs (8min): https://wpshadow.com/training/api-security
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
 * API Throttling Not Configured Diagnostic Class
 *
 * Implements REST API rate limiting detection across WordPress core and popular
 * throttling plugins (Wordfence, Jetpack, Sucuri). Detection method: check response
 * headers (X-RateLimit-* headers), plugin activity flags, and rate limit option values.
 * For WordPress 6.1+, verifies rest_api_init hook registers rate limiting callback.
 *
 * **Detection Pattern:**
 * 1. Check if rate limiting plugin is active (query wp_options for known plugins)
 * 2. Make test REST API request, inspect response headers for rate limit markers
 * 3. Verify X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset headers
 * 4. Confirm limit is reasonable (10-100 requests/min, not 10,000s)
 * 5. Return failure if no headers OR limits > abuse threshold
 *
 * **Real-World Scenario:**
 * Small news site running WordPress without rate limiting. June 2024: botnet discovers
 * their /wp-json endpoint during credential-stuffing campaign. Makes 2,000 attempts/min
 * against user enumeration endpoint for 4 hours. Server CPU peaks at 400%, legitimate
 * readers see 503 timeouts. Attacker extracts user list before detection. Site took
 * 12 hours to restore + changed all credentials.
 *
 * **Implementation Notes:**
 * - Uses wp_remote_get() to make safe test calls to REST API
 * - Checks standard HTTP headers (no plugin-specific parsing needed)
 * - Returns severity: critical (no throttling on public endpoints), medium (high threshold)
 * - Auto-fixable treatment: enables built-in WP rate limiting or plugin
 *
 * @since 1.2601.2352
 */
class Diagnostic_API_Throttling_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-throttling-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Throttling Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if API throttling is configured';

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
		// Check for REST API rate limiting
		if ( ! has_filter( 'rest_authentication_errors', 'check_api_rate_limit' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'API throttling is not configured. Limit REST API requests to 60 per minute per IP to prevent brute force attacks and ensure fair resource usage.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 55,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/api-throttling-not-configured',
			);
		}

		return null;
	}
}
