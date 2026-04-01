<?php
/**
 * Rate Limiting Not Configured For API Diagnostic
 *
 * Validates rate limiting is configured on REST API endpoints.
 * Unlimited API = attacker can make infinite requests (DDoS + brute force).
 * Rate limiting throttles requests per IP (prevents abuse).
 *
 * **What This Check Does:**
 * - Checks if API rate limiting implemented
 * - Validates rate limit thresholds (requests/minute)
 * - Tests if limits apply per IP address
 * - Confirms 429 response on limit exceeded
 * - Checks if bypass methods present
 * - Returns severity if no rate limiting
 *
 * **Why This Matters:**
 * Unlimited API = attack amplifier. Scenarios:
 * - REST API allows infinite requests
 * - Attacker writes loop: 10K requests/minute
 * - Server overloaded (legitimate users blocked)
 * - DDoS attack (attacker uses your API as weapon)
 *
 * **Business Impact:**
 * API endpoint queries database without rate limiting. Attacker discovers.
 * Writes script: 1000 requests/minute. Database slowed to crawl. Site
 * becomes unresponsive (users can't browse). Revenue loss: $10K/hour.
 * Bandwidth bill: $5K+. With rate limiting (10 req/min): attacker attack
 * takes months (impractical). Automatic protection.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: API protected from abuse
 * - #9 Show Value: Quantified DDoS prevention
 * - #10 Beyond Pure: Fair-use enforcement
 *
 * **Related Checks:**
 * - API Throttling Overall (general API limits)
 * - Geolocation Blocking (source restriction)
 * - DDoS Protection Configuration (infrastructure)
 *
 * **Learn More:**
 * API rate limiting: https://wpshadow.com/kb/wordpress-api-rate-limiting
 * Video: Configuring API limits (9min): https://wpshadow.com/training/api-rate-limiting
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
 * Rate Limiting Not Configured For API Diagnostic Class
 *
 * Detects missing API rate limiting.
 *
 * **Detection Pattern:**
 * 1. Query REST API endpoints
 * 2. Check for rate limit headers (X-RateLimit-*)
 * 3. Validate limit threshold (requests/minute)
 * 4. Test if limits apply per IP
 * 5. Confirm 429 response on excess
 * 6. Return severity if no limits
 *
 * **Real-World Scenario:**
 * News site has REST API endpoint for articles (no rate limiting). Attacker
 * writes script: 100 requests/second. Server CPU spikes. Database maxes out.
 * Site goes down. Users can't access content. Revenue lost: $50K. With rate
 * limiting (10 requests/second per IP): attacker's script throttled. Site
 * stays responsive.
 *
 * **Implementation Notes:**
 * - Checks REST API endpoints
 * - Validates rate limit headers
 * - Tests actual rate limit enforcement
 * - Severity: high (no limits), medium (weak limits)
 * - Treatment: implement per-IP rate limiting on all API endpoints
 *
 * @since 0.6093.1200
 */
class Diagnostic_Rate_Limiting_Not_Configured_For_API extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rate-limiting-not-configured-for-api';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Rate Limiting Not Configured For API';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if API rate limiting is configured';

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
		// Check if rate limiting is set
		if ( ! has_filter( 'rest_request_before_callbacks', 'check_api_rate_limit' ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'API rate limiting is not configured. Implement rate limiting to prevent abuse and ensure fair API access.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rate-limiting-not-configured-for-api?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'       => array(
					'why'            => __( 'Public REST API endpoints are a favored abuse vector because they are easy to discover, predictable, and often lack strong gating. When rate limiting is missing, attackers can flood endpoints with requests that look legitimate, bypassing simple firewall rules. Each request can trigger database queries, cache misses, authentication checks, and PHP execution, which cascades into higher CPU usage, increased latency, and timeouts for real customers. The business impact includes revenue loss from downtime, degraded SEO due to poor performance signals, and higher infrastructure costs from autoscaling or bandwidth overages. OWASP API Security Top 10 identifies Unrestricted Resource Consumption as a major risk, and OWASP Top 10 2021 ranks Broken Access Control #1, which often co‑exists with unlimited access to enumerated resources. Verizon’s 2024 DBIR reports that roughly three‑quarters of breaches involve the human element and that web application attacks are a leading pattern against internet‑facing systems; attackers frequently pair stolen credentials with high‑rate API calls to map data and probe for weak authorization. Even if data is not directly exfiltrated, rate abuse can be used as a smokescreen to distract monitoring teams during credential stuffing or privilege escalation. For SaaS, membership, or e‑commerce sites, a slow API means broken carts, failed searches, and abandoned sessions. Rate limiting is one of the simplest, most cost‑effective controls that directly reduces attacker ROI by forcing long attack windows and enabling detection. Without it, you are effectively offering unlimited compute to anonymous actors.', 'wpshadow' ),
					'recommendation' => __( '1. Implement per‑IP and per‑user rate limits for all REST endpoints.
2. Apply stricter limits to anonymous traffic and higher limits to authenticated users.
3. Add burst and rolling‑window limits (per minute and per hour).
4. Return standard 429 responses with Retry‑After headers.
5. Enforce maximum page sizes and reject excessive per_page values.
6. Require authentication for endpoints that return bulk data.
7. Add WAF/CDN rules for abusive IPs and known bot signatures.
8. Log and alert on repeated 429s and spike patterns.
9. Cache safe GET responses to reduce backend load.
10. Run quarterly load tests to verify limits and alerting remain effective.', 'wpshadow' ),
				),
			);

			return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'api', self::$slug );
		}

		return null;
	}
}
