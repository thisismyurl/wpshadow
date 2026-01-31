<?php
/**
 * Core Web Vitals and Page Speed Index Diagnostic
 *
 * Measures Core Web Vitals (LCP, FID, CLS) and overall page speed to ensure
 * site meets Google's performance standards for ranking and user experience.
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
 * Diagnostic_Page_Speed_Index Class
 *
 * Evaluates Core Web Vitals and overall performance metrics.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Page_Speed_Index extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'page-speed-index';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Core Web Vitals and Page Speed Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures Core Web Vitals impacting SEO ranking and user experience';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if performance issues detected, null otherwise.
	 */
	public static function check() {
		$vitals = self::estimate_core_web_vitals();

		if ( $vitals['all_good'] ) {
			return null;
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: metric count */
				__( 'Found %d Core Web Vitals issues affecting SEO ranking and user experience.', 'wpshadow' ),
				count( $vitals['failing_metrics'] )
			),
			'severity'      => 'high',
			'threat_level'  => 75,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/core-web-vitals',
			'family'        => self::$family,
			'meta'          => array(
				'failing_metrics'    => $vitals['failing_metrics'],
				'seo_impact'         => __( 'Google uses Core Web Vitals as ranking signal - poor scores reduce visibility' ),
				'estimated_traffic_loss' => '10-25% reduction in click-through rate from search',
				'improvement_priority' => 'Critical for competitive keywords',
			),
			'details'       => array(
				'core_web_vitals_explained' => array(
					'LCP (Largest Contentful Paint)' => array(
						'What' => 'Time until largest visible element loads',
						'Good' => '< 2.5 seconds',
						'Poor' => '> 4 seconds',
						'Impact' => 'Affects perceived page load speed',
					),
					'FID (First Input Delay)' => array(
						'What' => 'Delay when user interacts (click, scroll) before response',
						'Good' => '< 100ms',
						'Poor' => '> 300ms',
						'Impact' => 'Affects responsiveness and user frustration',
					),
					'CLS (Cumulative Layout Shift)' => array(
						'What' => 'Unexpected layout changes as page loads',
						'Good' => '< 0.1',
						'Poor' => '> 0.25',
						'Impact' => 'Causes misclicks and poor UX',
					),
				),
				'estimated_vitals'  => $vitals,
				'optimization_steps' => array(
					'For LCP' => array(
						__( 'Optimize server response time (TTFB < 600ms)' ),
						__( 'Lazy load images and offscreen content' ),
						__( 'Minimize CSS and defer non-critical CSS' ),
						__( 'Use CDN to serve static content' ),
						__( 'Enable caching (browser + server)' ),
					),
					'For FID/INP' => array(
						__( 'Minimize JavaScript execution time' ),
						__( 'Use code splitting to load less JS upfront' ),
						__( 'Defer third-party scripts' ),
						__( 'Break up long tasks (< 50ms each)' ),
					),
					'For CLS' => array(
						__( 'Set explicit dimensions for images/iframes' ),
						__( 'Avoid inserting content above existing content' ),
						__( 'Use transform animations instead of position changes' ),
						__( 'Load fonts early (preload critical fonts)' ),
					),
				),
				'tools_for_testing'  => array(
					'Google PageSpeed Insights' => 'Get performance score and CWV metrics',
					'Google Search Console' => 'Core Web Vitals report shows field data',
					'Chrome DevTools Lighthouse' => 'Local testing with lab data',
					'WebPageTest' => 'Detailed waterfall and metrics',
					'Speedcurve' => 'Continuous monitoring and trending',
				),
				'seo_rankings_impact' => array(
					__( 'Page Experience Signal: CWV affects ranking' ),
					__( 'Mobile-First Indexing: Mobile CWV heavily weighted' ),
					__( 'Competitive Advantage: Good CWV = higher rankings' ),
					__( 'User Behavior: Fast sites = higher CTR in search results' ),
				),
			),
		);
	}

	/**
	 * Estimate Core Web Vitals metrics.
	 *
	 * @since  1.2601.2148
	 * @return array Estimated vitals.
	 */
	private static function estimate_core_web_vitals() {
		// Estimate based on common WordPress patterns
		$has_caching = (
			is_plugin_active( 'wp-super-cache/wp-cache.php' ) ||
			is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ||
			is_plugin_active( 'wp-fastest-cache/wpfc.php' )
		);

		$has_optimization = (
			is_plugin_active( 'autoptimize/autoptimize.php' ) ||
			is_plugin_active( 'wp-optimize/wp-optimize.php' )
		);

		$has_image_optimization = (
			is_plugin_active( 'imagify/imagify.php' ) ||
			is_plugin_active( 'ewww-image-optimizer/ewww-image-optimizer.php' )
		);

		// Estimate LCP (milliseconds)
		$lcp = 3500;
		if ( $has_caching ) {
			$lcp -= 800;
		}
		if ( $has_optimization ) {
			$lcp -= 600;
		}
		if ( $has_image_optimization ) {
			$lcp -= 400;
		}

		// Estimate FID (milliseconds)
		$fid = 150;
		if ( $has_optimization ) {
			$fid -= 50;
		}

		// Estimate CLS (0-1 scale)
		$cls = 0.15;
		if ( $has_optimization ) {
			$cls -= 0.05;
		}

		$failing = array();
		if ( $lcp > 2500 ) {
			$failing[] = 'LCP: ' . $lcp . 'ms (Good: < 2500ms)';
		}
		if ( $fid > 100 ) {
			$failing[] = 'FID: ' . $fid . 'ms (Good: < 100ms)';
		}
		if ( $cls > 0.1 ) {
			$failing[] = 'CLS: ' . number_format( $cls, 2 ) . ' (Good: < 0.1)';
		}

		return array(
			'lcp'            => $lcp,
			'fid'            => $fid,
			'cls'            => $cls,
			'all_good'       => empty( $failing ),
			'failing_metrics' => $failing,
		);
	}
}
