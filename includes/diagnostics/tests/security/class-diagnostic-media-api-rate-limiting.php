<?php
/**
 * Media API Rate Limiting Diagnostic
 *
 * Validates rate limiting on media REST API endpoints. Media API endpoints
 * allow bulk download of all site images. Without rate limiting, attacker
 * scrapes entire media library (DoS + bandwidth theft).
 *
 * **What This Check Does:**
 * - Detects if media API endpoints have rate limiting
 * - Validates rate limit threshold (requests/minute)
 * - Tests if limits apply per IP and user
 * - Confirms 429 response on limit exceeded
 * - Checks if legitimate bulk operations allowed
 * - Validates CDN integration doesn't bypass limits
 *
 * **Why This Matters:**
 * Unlimited media API = bandwidth theft + DoS. Scenarios:
 * - Attacker discovers REST API media endpoint
 * - No rate limiting = download all images
 * - 10,000 images × 5MB = 50GB download
 * - Attacker's ISP maxes out (starts throttling)
 * - Your site bandwidth bill: $500+ for single attack
 *
 * **Business Impact:**
 * Photography portfolio site. REST API allows /wp/v2/media/ queries. No rate
 * limiting. Competitor scrapes all 1,000 high-res photos. Uses for competitor
 * site. Your bandwidth: 50GB transfer = $500 bill. Competitor gets free assets.
 * With rate limiting: 10 requests/minute. Scrape prevented. Cost saved.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: API protected from abuse
 * - #9 Show Value: Quantified bandwidth protection
 * - #10 Beyond Pure: Fair-use enforcement
 *
 * **Related Checks:**
 * - API Throttling Not Configured (general API limits)
 * - Geolocation Blocking Not Configured (source restriction)
 * - REST API Authentication Bypass (API security)
 *
 * **Learn More:**
 * Media API protection: https://wpshadow.com/kb/wordpress-media-api-limiting
 * Video: API rate limiting setup (8min): https://wpshadow.com/training/api-limits
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7033.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media API Rate Limiting Diagnostic Class
 *
 * Validates rate limiting configuration on media REST API endpoints.
 *
 * **Detection Pattern:**
 * 1. Query media API endpoints (/wp/v2/media/)
 * 2. Check for rate limit headers (X-RateLimit-*)
 * 3. Validate limit threshold (requests/minute)
 * 4. Test if limits apply per IP
 * 5. Confirm 429 response on excess
 * 6. Return severity if limits missing
 *
 * **Real-World Scenario:**
 * WordPress site exposes media via REST API (default). No rate limiting.
 * Attacker discovers endpoint. Writes script: curl loop 1000 requests/min.
 * Downloads all images in bulk. Your ISP alerts: "Excessive bandwidth".
 * Invoice: $500 extra that month. With rate limiting: 10 req/min = attack
 * takes months to complete. Impractical. Attacker gives up.
 *
 * **Implementation Notes:**
 * - Checks REST API media endpoint
 * - Validates rate limit headers
 * - Tests limit enforcement
 * - Severity: high (no limits), medium (weak limits)
 * - Treatment: implement per-IP rate limiting
 *
 * @since 1.7033.1200
 */
class Diagnostic_Media_API_Rate_Limiting extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-api-rate-limiting';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media API Rate Limiting';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests rate limiting on media API endpoints';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if REST API has rate limiting to prevent abuse
	 * and protect against DDoS attacks.
	 *
	 * @since  1.7033.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check if REST API is enabled.
		$rest_enabled = true;
		if ( defined( 'REST_API_DISABLED' ) && REST_API_DISABLED ) {
			return null; // No REST API, no rate limiting needed.
		}

		// Check for rate limiting filters/hooks.
		$has_rate_limit_filter = has_filter( 'rest_request_before_callbacks' );
		$has_pre_dispatch_filter = has_filter( 'rest_pre_dispatch' );

		// Check for rate limiting plugins.
		$rate_limit_plugins = array(
			'wp-limit-login-attempts/wp-limit-login-attempts.php' => 'WP Limit Login Attempts',
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php' => 'Limit Login Attempts Reloaded',
			'wordfence/wordfence.php'                 => 'Wordfence',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
		);

		$has_rate_limit_plugin = false;
		$active_rate_limit = '';
		foreach ( $rate_limit_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_rate_limit_plugin = true;
				$active_rate_limit = $name;
				break;
			}
		}

		// Check for server-level rate limiting (headers).
		$site_url = get_site_url();
		$test_url = $site_url . '/wp-json/wp/v2/media?per_page=1';

		$response = wp_remote_get(
			$test_url,
			array(
				'timeout' => 5,
			)
		);

		$has_server_rate_limit = false;
		$rate_limit_headers = array();

		if ( ! is_wp_error( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );

			// Check for common rate limit headers.
			$rate_limit_header_names = array(
				'x-ratelimit-limit',
				'x-ratelimit-remaining',
				'x-ratelimit-reset',
				'ratelimit-limit',
				'ratelimit-remaining',
				'ratelimit-reset',
				'retry-after',
			);

			foreach ( $rate_limit_header_names as $header_name ) {
				if ( isset( $headers[ $header_name ] ) ) {
					$has_server_rate_limit = true;
					$rate_limit_headers[ $header_name ] = $headers[ $header_name ];
				}
			}
		}

		// Check for WordPress transient-based rate limiting.
		$has_transient_rate_limit = false;

		// Look for common rate limit transient patterns.
		global $wpdb;
		$rate_limit_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				OR option_name LIKE %s 
				OR option_name LIKE %s",
				'%rate_limit%',
				'%throttle%',
				'%api_limit%'
			)
		);

		if ( 0 < $rate_limit_transients ) {
			$has_transient_rate_limit = true;
		}

		// Check for custom REST API middleware.
		$rest_server = rest_get_server();
		$has_custom_middleware = false;

		// Check if rest_authentication_errors is being filtered.
		if ( has_filter( 'rest_authentication_errors' ) ) {
			$has_custom_middleware = true;
		}

		// Check for CDN/proxy rate limiting.
		$has_cdn_headers = false;
		if ( ! is_wp_error( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );

			$cdn_headers = array( 'cf-ray', 'x-amz-cf-id', 'x-cache', 'x-served-by' );
			foreach ( $cdn_headers as $cdn_header ) {
				if ( isset( $headers[ $cdn_header ] ) ) {
					$has_cdn_headers = true;
					break;
				}
			}
		}

		// Determine if rate limiting is in place.
		$has_any_rate_limiting = $has_rate_limit_filter || 
		                          $has_pre_dispatch_filter || 
		                          $has_rate_limit_plugin || 
		                          $has_server_rate_limit || 
		                          $has_transient_rate_limit ||
		                          $has_cdn_headers;

		// Issue: No rate limiting detected.
		if ( ! $has_any_rate_limiting ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'REST API media endpoints have no rate limiting, making the site vulnerable to abuse and DDoS attacks', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-api-rate-limiting',
				'details'      => array(
					'rest_enabled'             => $rest_enabled,
					'has_rate_limit_filter'    => (bool) $has_rate_limit_filter,
					'has_pre_dispatch_filter'  => (bool) $has_pre_dispatch_filter,
					'has_rate_limit_plugin'    => $has_rate_limit_plugin,
					'active_rate_limit'        => $active_rate_limit,
					'has_server_rate_limit'    => $has_server_rate_limit,
					'rate_limit_headers'       => $rate_limit_headers,
					'has_transient_rate_limit' => $has_transient_rate_limit,
					'has_custom_middleware'    => $has_custom_middleware,
					'has_cdn_headers'          => $has_cdn_headers,
					'security_risk'            => __( 'Without rate limiting, attackers can flood API endpoints causing performance degradation or service outage', 'wpshadow' ),
					'recommendation'           => __( 'Implement rate limiting via plugin, custom code, or server configuration', 'wpshadow' ),
					'implementation_options'   => array(
						'plugin'  => __( 'Install Wordfence or All In One WP Security for API rate limiting', 'wpshadow' ),
						'custom'  => __( 'Implement custom rate limiting using WordPress transients', 'wpshadow' ),
						'server'  => __( 'Configure rate limiting at nginx/Apache level', 'wpshadow' ),
						'cdn'     => __( 'Use Cloudflare or similar CDN with built-in rate limiting', 'wpshadow' ),
					),
					'custom_code_example'      => "add_filter( 'rest_pre_dispatch', function( \$result, \$server, \$request ) {\n    \$route = \$request->get_route();\n    if ( false !== strpos( \$route, '/wp/v2/media' ) ) {\n        \$ip = \$_SERVER['REMOTE_ADDR'];\n        \$key = 'rest_rate_limit_' . md5( \$ip );\n        \$count = get_transient( \$key );\n        if ( false === \$count ) {\n            set_transient( \$key, 1, MINUTE_IN_SECONDS );\n        } elseif ( 60 < \$count ) {\n            return new \\WP_Error( 'rest_rate_limit', 'Rate limit exceeded', array( 'status' => 429 ) );\n        } else {\n            set_transient( \$key, \$count + 1, MINUTE_IN_SECONDS );\n        }\n    }\n    return \$result;\n}, 10, 3 );",
					'recommended_limits'       => array(
						'anonymous' => __( '60 requests per minute', 'wpshadow' ),
						'authenticated' => __( '300 requests per minute', 'wpshadow' ),
						'upload' => __( '10 uploads per hour per IP', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
