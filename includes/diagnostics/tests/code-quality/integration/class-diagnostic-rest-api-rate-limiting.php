<?php
/**
 * REST API Rate Limiting Diagnostic
 *
 * Verifies rate limiting protection on REST API endpoints
 * to prevent brute force attacks and resource exhaustion.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_REST_API_Rate_Limiting Class
 *
 * Verifies API rate limiting enabled.
 *
 * @since 1.2601.2148
 */
class Diagnostic_REST_API_Rate_Limiting extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-rate-limiting';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Rate Limiting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies API endpoint rate limiting';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'integration';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if rate limiting missing, null otherwise.
	 */
	public static function check() {
		$api_status = self::check_api_rate_limiting();

		if ( $api_status['is_protected'] ) {
			return null; // Rate limiting enabled
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'REST API has no rate limiting. Attackers can brute force user credentials, scrape all content, or DDoS your server with automated requests. Easy fix = critical security layer.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/rest-api-rate-limiting',
			'family'       => self::$family,
			'meta'         => array(
				'rate_limiting_detected' => false,
			),
			'details'      => array(
				'rest_api_attack_vectors'     => array(
					'Brute Force Users' => array(
						'Endpoint: /wp-json/wp/v2/users/',
						'Attack: 1000 login attempts/minute',
						'Result: Guesses password in minutes',
						'Prevention: Rate limit to 10/minute per IP',
					),
					'Content Scraping' => array(
						'Endpoint: /wp-json/wp/v2/posts?per_page=100',
						'Attack: Download all 5000 posts in 50 requests',
						'Result: Steal all content',
						'Prevention: Rate limit to 1 request/sec per IP',
					),
					'DDoS Attack' => array(
						'Endpoint: Any endpoint, many concurrent',
						'Attack: 10,000 requests/second',
						'Result: Server crashes',
						'Prevention: Cap at 100 requests/minute per IP',
					),
				),
				'wordpress_native_limits'     => array(
					'REST API Authentication' => array(
						'WordPress default: No limit',
						'Issue: Anyone can query without auth',
					),
					'REST API Actions' => array(
						'Disabled: Cookie/nonce auth by default',
						'Enabled: Basic auth, JWT plugins',
					),
					'Required: Third-party solution',
				),
				'rate_limiting_solutions'     => array(
					'Cloudflare (Recommended)' => array(
						'Cost: Free tier available',
						'Features: IP-based rate limiting',
						'Setup: DNS → Cloudflare',
						'Limits: 10 req/sec default',
					),
					'Nginx/Apache Rules' => array(
						'Nginx: limit_req module',
						'Apache: mod_ratelimit',
						'Per-IP: Track by visitor IP',
						'Setup: Server configuration',
					),
					'WordPress Plugins' => array(
						'API Rate Limiting plugin',
						'Jetpack: Built-in protection',
						'Sucuri: WAF + rate limiting',
					),
					'AWS/GCP' => array(
						'CloudFront: AWS CDN with rate limiting',
						'Cloud Armor: GCP WAF protection',
						'Cloud Load Balancer: Request limiting',
					),
				),
				'recommended_limits'          => array(
					'Authentication Endpoints' => array(
						'/wp-json/wp/v2/auth: 5 requests/minute',
						'Prevents: Brute force attacks',
					),
					'User Enumeration' => array(
						'/wp-json/wp/v2/users/: 10 requests/minute',
						'Prevents: Username discovery',
					),
					'Public Endpoints' => array(
						'/wp-json/wp/v2/posts/: 100 requests/minute',
						'Allow: Legitimate API consumers',
					),
					'Anonymous Visitors' => array(
						'Per-IP limit: Shared among visitors',
						'Example: 1000 requests/hour = 17/min',
					),
				),
				'implementing_cloudflare'     => array(
					__( '1. Sign up: Cloudflare.com (free)' ),
					__( '2. Add site: Points to Cloudflare NS' ),
					__( '3. WAF Rules: Security → WAF' ),
					__( '4. Rate Limiting: Security → Rate limiting' ),
					__( '5. Set: 20 req/20 sec per IP' ),
					__( '6. Verify: Test with curl -I yoursite.com' ),
				),
				'testing_rate_limiting'       => array(
					'Quick Test' => array(
						'curl: for i in {1..100}; do curl -s /wp-json/wp/v2/posts; done',
						'Result: After limit, get 429 error',
						'Tool: ab (Apache Bench) for load testing',
					),
					'Attack Simulation' => array(
						'Tool: OWASP ZAP or Burp Suite',
						'Verify: Rate limiting blocks attacks',
						'Monitor: Check request patterns',
					),
				),
			),
		);
	}

	/**
	 * Check API rate limiting.
	 *
	 * @since  1.2601.2148
	 * @return array API protection status.
	 */
	private static function check_api_rate_limiting() {
		$is_protected = false;

		// Check Cloudflare
		if ( ! empty( $_SERVER['HTTP_CF_RAY'] ) ) {
			$is_protected = true; // Behind Cloudflare
		}

		// Check rate limiting plugins
		if ( class_exists( 'Jetpack' ) && method_exists( 'Jetpack', 'is_active' ) && \Jetpack::is_active() ) {
			$is_protected = true; // Jetpack has built-in protection
		}

		// Check Sucuri plugin
		if ( is_plugin_active( 'sucuri/sucuri.php' ) ) {
			$is_protected = true; // Sucuri WAF active
		}

		return array(
			'is_protected' => $is_protected,
		);
	}
}
