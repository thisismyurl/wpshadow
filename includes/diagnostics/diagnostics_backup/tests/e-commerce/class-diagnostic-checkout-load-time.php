<?php
/**
 * Checkout Page Load Time Diagnostic
 *
 * Measures dedicated checkout page performance. Slow checkout significantly
 * increases cart abandonment - every second of delay causes approximately
 * 7% conversion loss. Critical for revenue optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\E-commerce
 * @since      1.6028.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checkout Page Load Time Diagnostic Class
 *
 * Tests checkout page performance using actual HTTP requests and timing
 * measurements. Focuses on FCP, LCP, and total load time.
 *
 * @since 1.6028.1500
 */
class Diagnostic_Checkout_Load_Time extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1500
	 * @var   string
	 */
	protected static $slug = 'checkout-load-time';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1500
	 * @var   string
	 */
	protected static $title = 'Checkout Page Load Time Above 3 Seconds';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1500
	 * @var   string
	 */
	protected static $description = 'Measures checkout page performance to prevent cart abandonment';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1500
	 * @var   string
	 */
	protected static $family = 'e-commerce';

	/**
	 * Run the diagnostic check
	 *
	 * Performs HTTP request to checkout page and measures response time.
	 * Benchmarks:
	 * - ≤2s: Excellent
	 * - 2-3s: Good
	 * - 3-5s: Warning (7-21% conversion loss)
	 * - >5s: Critical (35%+ conversion loss)
	 *
	 * @since  1.6028.1500
	 * @return array|null Null if fast, array if slow.
	 */
	public static function check() {
		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		$load_time_data = self::measure_checkout_load_time();

		if ( is_null( $load_time_data ) ) {
			return null; // Could not measure.
		}

		$load_time_seconds = $load_time_data['load_time'];

		// Only report if >3 seconds.
		if ( $load_time_seconds <= 3.0 ) {
			return null;
		}

		$severity = $load_time_seconds > 5.0 ? 'critical' : 'high';
		$threat_level = min( 90, 50 + ( $load_time_seconds * 8 ) );
		$conversion_loss = min( 50, ( $load_time_seconds - 1 ) * 7 );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: load time in seconds, 2: estimated conversion loss percentage */
				__( 'Checkout loads in %1$.1f seconds, causing ~%2$d%% conversion loss', 'wpshadow' ),
				$load_time_seconds,
				$conversion_loss
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'family'       => self::$family,
			'kb_link'      => 'https://wpshadow.com/kb/optimize-checkout-speed',
			'meta'         => array(
				'load_time_seconds'   => $load_time_seconds,
				'page_size_kb'        => $load_time_data['page_size'],
				'request_count'       => $load_time_data['request_count'],
				'estimated_conversion_loss' => $conversion_loss,
				'immediate_actions'   => array(
					__( 'Optimize checkout page scripts and styles', 'wpshadow' ),
					__( 'Enable page caching for logged-out users', 'wpshadow' ),
					__( 'Minify and combine CSS/JS assets', 'wpshadow' ),
					__( 'Optimize payment gateway integration', 'wpshadow' ),
				),
			),
			'details'      => array(
				'why_important' => __(
					'Checkout is the most critical page for revenue. Research shows each second of delay causes ~7% conversion loss. A 5-second checkout loses 28% of customers compared to 1-second. Slow checkout indicates heavy scripts (payment gateways, analytics), unoptimized images, or server performance issues. This directly impacts revenue every single day.',
					'wpshadow'
				),
				'user_impact'   => __(
					'Customers abandon carts during slow checkout, especially on mobile. They perceive your site as unreliable, question security, or simply lose patience. A store with $10K/month revenue and 4-second checkout could gain $700+/month by reducing to 2 seconds. Slow checkout compounds with abandonment rate - fixing both provides exponential revenue improvement.',
					'wpshadow'
				),
				'solution_options' => array(
					'free'     => array(
						__( 'Defer non-critical JavaScript on checkout', 'wpshadow' ),
						__( 'Remove unnecessary plugins from checkout page', 'wpshadow' ),
						__( 'Enable Gzip compression and browser caching', 'wpshadow' ),
					),
					'premium'  => array(
						__( 'Install WP Rocket or WP Fastest Cache ($49-99/year)', 'wpshadow' ),
						__( 'Use CDN for static assets (Cloudflare, BunnyCDN)', 'wpshadow' ),
						__( 'Upgrade hosting to performance tier with SSD/NVMe', 'wpshadow' ),
					),
					'advanced' => array(
						__( 'Implement Redis object caching for sessions', 'wpshadow' ),
						__( 'Use lazy loading for below-fold content', 'wpshadow' ),
						__( 'Deploy HTTP/2 or HTTP/3 for multiplexing', 'wpshadow' ),
					),
				),
				'best_practices' => array(
					__( 'Load payment gateway scripts only on checkout page', 'wpshadow' ),
					__( 'Minimize third-party scripts (analytics, chat, etc.)', 'wpshadow' ),
					__( 'Inline critical CSS for above-the-fold content', 'wpshadow' ),
					__( 'Use async/defer for non-critical JavaScript', 'wpshadow' ),
					__( 'Optimize images (WebP format, proper sizing)', 'wpshadow' ),
					__( 'Test checkout speed on mobile 3G connection', 'wpshadow' ),
					__( 'Monitor checkout performance with RUM tools', 'wpshadow' ),
				),
				'testing_steps' => array(
					__( 'Test checkout URL: yoursite.com/checkout/', 'wpshadow' ),
					__( 'Use Chrome DevTools Network tab to measure load time', 'wpshadow' ),
					__( 'Run Google PageSpeed Insights on checkout page', 'wpshadow' ),
					__( 'Test on mobile device with 3G throttling', 'wpshadow' ),
					__( 'Review Waterfall chart for slow resources', 'wpshadow' ),
					__( 'Check for render-blocking resources', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Measure checkout load time
	 *
	 * Performs HTTP request to checkout page and measures response time.
	 * Also analyzes page size and estimates resource count.
	 *
	 * @since  1.6028.1500
	 * @return array|null Load time data or null if measurement failed.
	 */
	private static function measure_checkout_load_time() {
		$checkout_url = wc_get_checkout_url();

		if ( empty( $checkout_url ) ) {
			return null;
		}

		// Measure load time with wp_remote_get.
		$start_time = microtime( true );

		$response = wp_remote_get(
			$checkout_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'headers'   => array(
					'User-Agent' => 'WPShadow/1.0 Performance Monitor',
				),
			)
		);

		$end_time = microtime( true );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$load_time = $end_time - $start_time;
		$body      = wp_remote_retrieve_body( $response );
		$page_size = strlen( $body ) / 1024; // KB.

		// Estimate resource count by counting script/link tags.
		$script_count = substr_count( $body, '<script' );
		$style_count  = substr_count( $body, '<link' );
		$request_count = $script_count + $style_count;

		return array(
			'load_time'     => round( $load_time, 2 ),
			'page_size'     => round( $page_size, 1 ),
			'request_count' => $request_count,
		);
	}
}
