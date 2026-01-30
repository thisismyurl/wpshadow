<?php
/**
 * Server Response Time Diagnostic
 *
 * Measures Time to First Byte (TTFB) indicating server hardware,
 * PHP configuration, and plugin performance issues.
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
 * Diagnostic_Server_Response_Time Class
 *
 * Monitors server response time.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Server_Response_Time extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'server-response-time';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Server Response Time';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures Time to First Byte (TTFB)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'health';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if TTFB slow, null otherwise.
	 */
	public static function check() {
		$response_time = self::measure_response_time();

		if ( $response_time['ttfb'] < 600 ) {
			return null; // Good response time
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %dms: milliseconds */
				__( 'Server response time is %dms. Slow TTFB indicates server overload or PHP inefficiency. Every 100ms delay = 1% conversion loss. TTFB > 1s = unacceptable.', 'wpshadow' ),
				$response_time['ttfb']
			),
			'severity'     => $response_time['severity'],
			'threat_level' => $response_time['threat_level'],
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/server-response-time',
			'family'       => self::$family,
			'meta'         => array(
				'ttfb_ms'       => $response_time['ttfb'],
				'assessment'    => $response_time['assessment'],
			),
			'details'      => array(
				'what_is_ttfb'                => array(
					__( 'Time to First Byte = from request to first server response' ),
					__( 'Includes: Server processing, database queries, PHP execution' ),
					__( 'Excludes: Browser download time, asset rendering' ),
					__( 'Benchmark: < 600ms is good, < 200ms is excellent' ),
				),
				'ttfb_benchmarks'             => array(
					'< 200ms' => 'Excellent (optimized)',
					'200-600ms' => 'Good (acceptable)',
					'600-1000ms' => 'Poor (optimization needed)',
					'1000-2000ms' => 'Very Poor (critical)',
					'> 2000ms' => 'Critical (site broken)',
				),
				'common_ttfb_causes'          => array(
					'Hosting Quality' => array(
						'Cheap shared hosting: 1-3s TTFB',
						'Good shared hosting: 300-600ms',
						'Dedicated/VPS: 100-300ms',
						'Cloud (AWS/GCP): 50-200ms',
					),
					'Plugin Load' => array(
						'Each plugin adds 5-20ms',
						'20 plugins = 100-400ms overhead',
						'Solution: Audit and remove unused plugins',
					),
					'Database' => array(
						'Slow queries: 50-500ms per query',
						'Unindexed tables: Exponential slowness',
						'Solution: Optimize queries, add indexes',
					),
					'PHP Version' => array(
						'PHP 5.6: 30% slower than 8.0',
						'PHP 7.4: 15% slower than 8.0',
						'PHP 8.0+: Fastest, 2-3x improvement',
					),
				),
				'measuring_ttfb'              => array(
					'Google PageSpeed Insights' => array(
						'URL: pagespeed.web.dev',
						'Tab: "Insights" (top right)',
						'Shows: "Server response time (TTFB)"',
					),
					'Chrome DevTools' => array(
						'Open: DevTools (F12) → Network',
						'Load page: See timing breakdown',
						'Look: "Waiting for server response" (TTFB portion)',
					),
					'WebPageTest' => array(
						'URL: webpagetest.org',
						'Shows: Detailed TTFB by browser/location',
					),
				),
				'improving_ttfb'              => array(
					'Upgrade Hosting' => array(
						'Shared → VPS: 40-60% improvement',
						'VPS → Cloud: 50-70% improvement',
						'Cost: $5/mo → $10-30/mo',
					),
					'Enable Caching' => array(
						'Object cache: Redis/Memcached (20-30% improvement)',
						'Page cache: WP Super Cache (30-50%)',
						'Combined: 50-80% TTFB reduction',
					),
					'Optimize Database' => array(
						'Delete unused data: Old revisions, spam',
						'Add indexes: wp_postmeta (meta_key)',
						'Regular maintenance: Monthly cleanup',
					),
					'Reduce Plugins' => array(
						'Audit: Keep 20 or fewer',
						'Remove: Unused, outdated plugins',
						'Disable on pages: Use plugin settings',
					),
				),
				'caching_strategies'         => array(
					'Full Page Cache' => array(
						'Stores: Rendered HTML',
						'TTL: 1 hour (or browser invalidation)',
						'Plugin: WP Super Cache, W3 Total Cache',
						'Improvement: 60-90% TTFB',
					),
					'Object Cache' => array(
						'Stores: Database query results',
						'TTL: 1-24 hours',
						'Requires: Redis or Memcached',
						'Improvement: 30-60% TTFB',
					),
				),
			),
		);
	}

	/**
	 * Measure server response time.
	 *
	 * @since  1.2601.2148
	 * @return array Response time analysis.
	 */
	private static function measure_response_time() {
		$start = microtime( true );
		$response = wp_remote_get( home_url(), array( 'timeout' => 10 ) );
		$elapsed = ( microtime( true ) - $start ) * 1000; // Convert to ms

		if ( is_wp_error( $response ) ) {
			$ttfb = 9999; // Error
			$severity = 'critical';
			$threat_level = 90;
			$assessment = 'Error - cannot measure';
		} elseif ( $elapsed < 200 ) {
			$ttfb = (int) $elapsed;
			$severity = 'info';
			$threat_level = 10;
			$assessment = 'Excellent';
		} elseif ( $elapsed < 600 ) {
			$ttfb = (int) $elapsed;
			$severity = 'info';
			$threat_level = 20;
			$assessment = 'Good';
		} elseif ( $elapsed < 1000 ) {
			$ttfb = (int) $elapsed;
			$severity = 'medium';
			$threat_level = 50;
			$assessment = 'Poor - optimization needed';
		} elseif ( $elapsed < 2000 ) {
			$ttfb = (int) $elapsed;
			$severity = 'high';
			$threat_level = 75;
			$assessment = 'Very Poor - critical';
		} else {
			$ttfb = (int) $elapsed;
			$severity = 'critical';
			$threat_level = 90;
			$assessment = 'Critical - upgrade needed';
		}

		return array(
			'ttfb'         => $ttfb,
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'assessment'   => $assessment,
		);
	}
}
