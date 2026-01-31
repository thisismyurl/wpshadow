<?php
/**
 * REST API Rate Limiting Diagnostic
 *
 * Detects lack of REST API rate limiting, leaving endpoints vulnerable
 * to abuse, DDoS attacks, and resource exhaustion.
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
 * Detects missing REST API rate limiting.
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
	protected static $description = 'Detects lack of REST API rate limiting';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if rate limiting missing, null otherwise.
	 */
	public static function check() {
		$rate_limit_check = self::check_rate_limiting();

		if ( $rate_limit_check['is_protected'] ) {
			return null; // Rate limiting enabled
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'REST API has no rate limiting. Attackers send 1000s of requests = server overload, site down. Abuse endpoints for brute force, data scraping, DDoS.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/rest-api-rate-limiting',
			'family'       => self::$family,
			'meta'         => array(
				'rest_api_enabled'    => rest_url(),
				'protection_method'   => $rate_limit_check['method'],
			),
			'details'      => array(
				'rest_api_abuse_scenarios'  => array(
					'Brute Force Attacks' => array(
						'Target: /wp-json/wp/v2/users (user enumeration)',
						'Attack: 1000 requests/second',
						'Result: All usernames exposed',
					),
					'Content Scraping' => array(
						'Target: /wp-json/wp/v2/posts?per_page=100',
						'Attack: Download entire site content',
						'Result: Content theft, plagiarism',
					),
					'Resource Exhaustion' => array(
						'Target: Any expensive endpoint',
						'Attack: Rapid repeated requests',
						'Result: CPU/memory overload, site crash',
					),
				),
				'rate_limiting_strategies'  => array(
					'Per-IP Limiting' => array(
						'Example: 60 requests per minute per IP',
						'Blocks: Single attacker/bot',
						'Limitation: VPNs can evade',
					),
					'Per-User Limiting' => array(
						'Example: 100 requests per minute per user',
						'Blocks: Abusive authenticated users',
						'Best for: Logged-in API access',
					),
					'Global Limiting' => array(
						'Example: 1000 requests per minute site-wide',
						'Blocks: DDoS attacks',
						'Protection: Server resources',
					),
				),
				'implementing_rate_limiting' => array(
					'Cloudflare Rate Limiting' => array(
						'Free tier: 10 rules',
						'Example: /wp-json/* → 60 req/min',
						'Response: 429 Too Many Requests',
						'Setup: Dashboard → Security → WAF → Rate Limiting',
					),
					'Nginx Rate Limiting' => array(
						'Config: limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;',
						'Location: /wp-json/',
						'Apply: limit_req zone=api burst=10;',
					),
					'Apache mod_ratelimit' => array(
						'Enable: a2enmod ratelimit',
						'.htaccess: SetOutputFilter RATE_LIMIT',
						'SetEnv rate-limit 100',
					),
					'WordPress Plugin' => array(
						'WP REST API Rate Limiting (free)',
						'Settings: 60 requests per minute',
						'Per-IP or per-user',
					),
				),
				'disabling_unused_endpoints' => array(
					'Disable User Enumeration' => array(
						'add_filter(\'rest_endpoints\', function($endpoints) {',
						'  if (isset($endpoints[\'/wp/v2/users\'])) {',
						'    unset($endpoints[\'/wp/v2/users\']);',
						'  }',
						'  return $endpoints;',
						'});',
					),
					'Disable for Logged Out Users' => array(
						'add_filter(\'rest_authentication_errors\', function($result) {',
						'  if (!is_user_logged_in()) {',
						'    return new WP_Error(\'rest_disabled\', \'REST API disabled\', array(\'status\' => 401));',
						'  }',
						'  return $result;',
						'});',
					),
				),
				'monitoring_api_usage'      => array(
					__( 'Check server access logs: grep "wp-json" /var/log/apache2/access.log' ),
					__( 'Monitor 429 responses (rate limit hits)' ),
					__( 'Use Cloudflare Analytics for API traffic' ),
					__( 'Set up alerts for unusual spike in API requests' ),
				),
			),
		);
	}

	/**
	 * Check rate limiting.
	 *
	 * @since  1.2601.2148
	 * @return array Rate limiting status.
	 */
	private static function check_rate_limiting() {
		// Check for Cloudflare (often includes rate limiting)
		$response = wp_remote_head( home_url() );
		if ( ! is_wp_error( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );
			if ( isset( $headers['server'] ) && stripos( $headers['server'], 'cloudflare' ) !== false ) {
				return array(
					'is_protected' => true,
					'method'       => 'Cloudflare',
				);
			}
		}

		// Check for rate limiting plugin
		if ( is_plugin_active( 'wp-rest-api-rate-limiting/wp-rest-api-rate-limiting.php' ) ) {
			return array(
				'is_protected' => true,
				'method'       => 'WP REST API Rate Limiting Plugin',
			);
		}

		return array(
			'is_protected' => false,
			'method'       => 'None',
		);
	}
}
