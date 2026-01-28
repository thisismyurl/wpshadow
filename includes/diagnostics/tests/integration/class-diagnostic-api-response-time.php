<?php
/**
 * API Response Time Diagnostic
 *
 * Monitors external API performance to detect slow
 * third-party service integrations affecting page load.
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
 * Diagnostic_API_Response_Time Class
 *
 * Monitors external API performance.
 *
 * @since 1.2601.2148
 */
class Diagnostic_API_Response_Time extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-response-time';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Response Time';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors external API performance';

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
	 * @return array|null Finding array if APIs slow, null otherwise.
	 */
	public static function check() {
		$api_status = self::check_api_performance();

		if ( ! $api_status['has_issue'] ) {
			return null; // APIs responsive
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: slow API name */
				__( '%s API responds in %dms (slow). Each page load waits for API = slow page. Visitors leave before page loads = lost conversions. Timeout = feature broken.', 'wpshadow' ),
				$api_status['slowest_api'],
				$api_status['slowest_time']
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/api-response-time',
			'family'       => self::$family,
			'meta'         => array(
				'slowest_api'  => $api_status['slowest_api'],
				'response_ms'  => $api_status['slowest_time'],
			),
			'details'      => array(
				'how_apis_block_page_load'    => array(
					__( 'Synchronous call = Page waits for API response' ),
					__( 'Example: Load Google Analytics = 2000ms delay' ),
					__( 'Visitor: "Why is this site so slow?"' ),
					__( 'Bounce: Click back if > 3 seconds' ),
					__( 'Solution: Async load or cache' ),
				),
				'common_slow_apis'            => array(
					'Google Services' => array(
						'Google Analytics: 1000-3000ms',
						'Google Ads: 500-1500ms',
						'Cause: Geographic distance, network',
					),
					'Facebook Pixel' => array(
						'Loading: 800-2000ms',
						'Tracking: After page renders',
					),
					'Email Services' => array(
						'Mailchimp API: 300-1000ms',
						'ConvertKit API: 500-1500ms',
						'ConvertKit especially slow',
					),
					'Payment Gateways' => array(
						'Stripe: 200-500ms (fast)',
						'PayPal: 500-1200ms',
						'Square: 300-800ms',
					),
					'Geolocation' => array(
						'MaxMind: 100-500ms (local)',
						'IP2Location: 50-300ms',
					),
				),
				'api_performance_benchmark'    => array(
					'< 200ms' => 'Excellent - no impact',
					'200-500ms' => 'Good - acceptable',
					'500-1000ms' => 'Poor - noticeable',
					'1000-2000ms' => 'Very Poor - significant',
					'> 2000ms' => 'Critical - major problem',
				),
				'measuring_api_performance'    => array(
					'Browser DevTools' => array(
						'Network tab: See each API request',
						'Time: "waiting for server" = API time',
						'Filter: Find specific API calls',
					),
					'WordPress Debug' => array(
						'Plugin: Query Monitor',
						'Shows: HTTP requests + timing',
						'Or: Add timing code manually',
					),
					'Curl Command' => array(
						'time curl -w "@curl-format.txt" -o /dev/null https://api.service.com',
						'Shows: Total time + breakdown',
					),
				),
				'optimizing_slow_apis'        => array(
					'Asynchronous Loading' => array(
						'Load after page renders',
						'JavaScript: Defer non-critical scripts',
						'Google Analytics: async tag',
						'Benefit: Page loads fast, API loads after',
					),
					'Caching API Results' => array(
						'WordPress: Transients cache API response',
						'Redis: Fast in-memory cache',
						'TTL: 1 hour or 1 day depending on data',
						'Benefit: Skip API call on cache hit',
					),
					'Timeout Short' => array(
						'Set: Timeout 1000ms for external APIs',
						'Fallback: Default behavior if timeout',
						'Prevents: Waiting for hung APIs',
					),
					'Edge Cases (Advanced)' => array(
						'CDN caching: Cache API responses at CDN',
						'Service worker: Cache in browser',
					),
				),
				'monitoring_api_performance'   => array(
					__( 'Monthly: Check API response times' ),
					__( 'Compare: Track trends over time' ),
					__( 'Alert: If slowness detected' ),
					__( 'Communicate: Tell provider about issues' ),
				),
			),
		);
	}

	/**
	 * Check API performance.
	 *
	 * @since  1.2601.2148
	 * @return array API performance status.
	 */
	private static function check_api_performance() {
		$apis = array(
			'Google Analytics' => 'https://www.google-analytics.com/ga.js',
			'Google Ads' => 'https://www.googletagmanager.com/gtm.js',
		);

		$slowest_api = '';
		$slowest_time = 0;
		$has_issue = false;

		foreach ( $apis as $name => $url ) {
			$start = microtime( true );
			$response = wp_remote_head( $url, array( 'timeout' => 5 ) );
			$elapsed = ( microtime( true ) - $start ) * 1000;

			if ( is_wp_error( $response ) ) {
				continue;
			}

			if ( $elapsed > $slowest_time ) {
				$slowest_time = (int) $elapsed;
				$slowest_api = $name;
				if ( $elapsed > 1000 ) {
					$has_issue = true;
				}
			}
		}

		return array(
			'has_issue'     => $has_issue,
			'slowest_api'   => $slowest_api,
			'slowest_time'  => $slowest_time,
		);
	}
}
