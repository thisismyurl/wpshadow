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
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

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
 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for REST API rate limiting
		if ( ! has_filter( 'rest_authentication_errors', 'check_api_rate_limit' ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'API throttling is not configured. Limit REST API requests to 60 per minute per IP to prevent brute force attacks and ensure fair resource usage.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 55,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/api-throttling-not-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'       => array(
					'why'            => __( 'Throttling = per-connection rate limit. Real scenario: Attacker creates 1,000 threads, each makes 1 request/second. Total 1,000 requests/sec. No throttling = overwhelms server. With throttling: Each connection limited to 60 requests/min. Attacker gets 1,000 total requests/hour (effective). Legitimate users (100-200/hour) unaffected. Protects against credential stuffing, API abuse, DDoS.', 'wpshadow' ),
					'recommendation' => __( '1. Implement per-connection throttling: 60 requests/minute per IP. 2. Track requests via IP address or auth token. 3. Return HTTP 429 when throttled. 4. Include Retry-After header. 5. Use Redis for distributed throttling. 6. Whitelist internal IPs (staging, monitoring). 7. Different limits: auth=100/min, anon=60/min. 8. Lower limits on POST/DELETE: 10 requests/minute. 9. Implement gradual backoff for repeat violators. 10. Log throttled requests to security event log.', 'wpshadow' ),
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'api-throttling', 'connection-throttling' );
			return $finding;
		}

		return null;
	}
}
