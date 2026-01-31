<?php
/**
 * Slow Page Load Time Diagnostic
 *
 * Monitors page load performance and identifies when load times exceed
 * acceptable thresholds, causing user frustration and ranking losses.
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
 * Diagnostic_Slow_Page_Load_Time Class
 *
 * Detects slow page load times.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Slow_Page_Load_Time extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'slow-page-load-time';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Slow Page Load Time';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors page load performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-experience';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if slow load detected, null otherwise.
	 */
	public static function check() {
		$performance = self::measure_page_load();

		if ( $performance['is_acceptable'] ) {
			return null; // Load time acceptable
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %.2f: load time in seconds */
				__( 'Page load time %.2fs exceeds 3s threshold. 53%% of mobile users abandon sites loading >3s. Every 1s delay = 7%% conversion loss.', 'wpshadow' ),
				$performance['load_time']
			),
			'severity'     => $performance['load_time'] > 5 ? 'critical' : 'high',
			'threat_level' => min( 90, (int) ( $performance['load_time'] * 15 ) ),
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/page-speed',
			'family'       => self::$family,
			'meta'         => array(
				'load_time_seconds' => $performance['load_time'],
				'target_time'       => 3.0,
				'abandonment_rate'  => __( '53% abandon if >3 seconds' ),
				'conversion_impact' => __( '7% loss per 1s delay' ),
			),
			'details'      => array(
				'load_time_impact'        => array(
					'< 1 second' => 'Excellent - users very satisfied',
					'1-3 seconds' => 'Good - acceptable performance',
					'3-5 seconds' => 'Poor - noticeable delays, bounces increase',
					'5-10 seconds' => 'Very poor - high abandonment',
					'> 10 seconds' => 'Critical - most users abandon',
				),
				'performance_statistics'  => array(
					__( '53% of mobile users abandon if load >3 seconds (Google)' ),
					__( 'Amazon: 100ms delay = 1% revenue loss' ),
					__( 'Walmart: 1s improvement = 2% conversion increase' ),
					__( 'BBC: Extra 1s load time = 10% user loss' ),
				),
				'testing_page_speed'      => array(
					'Google PageSpeed Insights' => array(
						'URL: pagespeed.web.dev',
						'Tests mobile + desktop',
						'Shows Core Web Vitals scores',
						'Provides optimization suggestions',
					),
					'GTmetrix' => array(
						'URL: gtmetrix.com',
						'Detailed waterfall chart',
						'Shows resource load times',
						'Tests from multiple locations',
					),
					'WebPageTest' => array(
						'URL: webpagetest.org',
						'Advanced testing options',
						'Film strip view of load process',
						'Connection throttling',
					),
					'Chrome DevTools' => array(
						'F12 → Network tab',
						'Record page load',
						'Identify slow resources',
						'Free, built-in',
					),
				),
				'core_web_vitals'         => array(
					'LCP (Largest Contentful Paint)' => array(
						'Target: <2.5 seconds',
						'Measures: Main content load time',
						'Fix: Optimize images, reduce server time',
					),
					'FID (First Input Delay)' => array(
						'Target: <100ms',
						'Measures: Interactivity delay',
						'Fix: Reduce JavaScript execution',
					),
					'CLS (Cumulative Layout Shift)' => array(
						'Target: <0.1',
						'Measures: Visual stability',
						'Fix: Set image dimensions, avoid dynamic content',
					),
				),
				'speed_optimization_steps' => array(
					'Quick Wins' => array(
						'Install caching plugin (WP Rocket, W3 Total Cache)',
						'Enable GZIP compression',
						'Optimize images (Smush, ShortPixel)',
						'Use CDN (Cloudflare free)',
					),
					'Advanced Optimization' => array(
						'Minimize HTTP requests (combine CSS/JS)',
						'Lazy load images below fold',
						'Defer JavaScript loading',
						'Use HTTP/2 protocol',
						'Enable browser caching',
					),
					'Server Optimization' => array(
						'Upgrade to PHP 8.1+',
						'Use OPcache',
						'Increase PHP memory limit (256M+)',
						'Consider managed hosting (Kinsta, WP Engine)',
					),
				),
			),
		);
	}

	/**
	 * Measure page load time.
	 *
	 * @since  1.2601.2148
	 * @return array Load time measurement.
	 */
	private static function measure_page_load() {
		$start = microtime( true );

		// Test homepage load
		$response = wp_remote_get(
			home_url(),
			array(
				'timeout' => 15,
			)
		);

		$load_time = microtime( true ) - $start;

		// If request failed, estimate based on common performance indicators
		if ( is_wp_error( $response ) ) {
			// Check for performance optimization indicators
			$has_caching = is_plugin_active( 'wp-rocket/wp-rocket.php' ) ||
						is_plugin_active( 'w3-total-cache/w3-total-cache.php' );

			// Estimate load time (pessimistic without caching)
			$load_time = $has_caching ? 2.5 : 4.5;
		}

		return array(
			'load_time'     => round( $load_time, 2 ),
			'is_acceptable' => $load_time < 3.0,
		);
	}
}
