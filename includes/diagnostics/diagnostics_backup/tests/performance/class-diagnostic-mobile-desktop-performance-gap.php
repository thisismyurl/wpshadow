<?php
/**
 * Mobile vs Desktop Performance Gap Diagnostic
 *
 * Compares performance scores between mobile and desktop to identify
 * mobile-specific optimization needs. Large gaps indicate responsive design
 * issues, unoptimized images, or JavaScript that blocks mobile rendering.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6028.1515
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Desktop Performance Gap Diagnostic Class
 *
 * Analyzes performance disparity between mobile and desktop experiences
 * to prioritize mobile optimization.
 *
 * @since 1.6028.1515
 */
class Diagnostic_Mobile_Desktop_Performance_Gap extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1515
	 * @var   string
	 */
	protected static $slug = 'mobile-desktop-performance-gap';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1515
	 * @var   string
	 */
	protected static $title = 'Mobile vs Desktop Performance Gap';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1515
	 * @var   string
	 */
	protected static $description = 'Compares mobile and desktop performance to identify mobile issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1515
	 * @var   string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check
	 *
	 * Measures load time on both mobile and desktop user agents.
	 * Benchmarks:
	 * - Mobile ≥80% of desktop: Good
	 * - 50-80%: Warning
	 * - <50%: Critical
	 *
	 * @since  1.6028.1515
	 * @return array|null Null if gap acceptable, array if large disparity.
	 */
	public static function check() {
		$performance_data = self::measure_performance_gap();

		if ( is_null( $performance_data ) ) {
			return null; // Cannot measure.
		}

		$gap_percentage = $performance_data['gap_percentage'];

		// Only report if mobile is significantly slower (<80% of desktop).
		if ( $gap_percentage >= 80 ) {
			return null;
		}

		$severity = $gap_percentage < 50 ? 'high' : 'medium';
		$threat_level = min( 75, 30 + ( ( 100 - $gap_percentage ) * 0.6 ) );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: mobile performance as percentage of desktop */
				__( 'Mobile performance is only %d%% of desktop speed, indicating mobile-specific issues', 'wpshadow' ),
				$gap_percentage
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'family'       => self::$family,
			'kb_link'      => 'https://wpshadow.com/kb/optimize-mobile-performance',
			'meta'         => array(
				'mobile_load_time'   => $performance_data['mobile_load_time'],
				'desktop_load_time'  => $performance_data['desktop_load_time'],
				'gap_percentage'     => $gap_percentage,
				'gap_seconds'        => $performance_data['gap_seconds'],
				'immediate_actions'  => array(
					__( 'Optimize images for mobile viewport sizes', 'wpshadow' ),
					__( 'Defer JavaScript execution on mobile', 'wpshadow' ),
					__( 'Implement responsive image srcset attributes', 'wpshadow' ),
					__( 'Test on real mobile devices (not just DevTools)', 'wpshadow' ),
				),
			),
			'details'      => array(
				'why_important' => __(
					'Mobile traffic represents 60-70% of web traffic and Google uses mobile-first indexing for rankings. Large mobile/desktop performance gaps indicate unoptimized mobile experience, hurting SEO, conversions, and user satisfaction. Mobile users have higher expectations for speed - they won\'t wait as long as desktop users.',
					'wpshadow'
				),
				'user_impact'   => __(
					'Mobile users experience frustratingly slow load times, leading to immediate abandonment. Your mobile conversion rate suffers, mobile SEO rankings drop, and you lose the majority of potential customers. Google\'s mobile-first index prioritizes mobile performance, so desktop-only optimization is insufficient.',
					'wpshadow'
				),
				'solution_options' => array(
					'free'     => array(
						__( 'Use responsive images with srcset attribute', 'wpshadow' ),
						__( 'Defer non-critical JavaScript loading', 'wpshadow' ),
						__( 'Reduce mobile viewport images to appropriate sizes', 'wpshadow' ),
					),
					'premium'  => array(
						__( 'Install WP Rocket with mobile-specific optimization', 'wpshadow' ),
						__( 'Use EWWW Image Optimizer for automatic mobile images', 'wpshadow' ),
						__( 'Deploy mobile CDN with image optimization', 'wpshadow' ),
					),
					'advanced' => array(
						__( 'Implement adaptive loading based on connection speed', 'wpshadow' ),
						__( 'Use WebP with AVIF fallback for mobile', 'wpshadow' ),
						__( 'Deploy service worker for offline mobile experience', 'wpshadow' ),
					),
				),
				'best_practices' => array(
					__( 'Test on real mobile devices (iPhone, Android)', 'wpshadow' ),
					__( 'Use Chrome DevTools mobile throttling (3G/4G)', 'wpshadow' ),
					__( 'Serve smaller images to mobile viewports', 'wpshadow' ),
					__( 'Minimize JavaScript execution on mobile', 'wpshadow' ),
					__( 'Use system fonts on mobile to avoid font loading', 'wpshadow' ),
					__( 'Implement lazy loading for below-fold content', 'wpshadow' ),
					__( 'Test Core Web Vitals specifically on mobile', 'wpshadow' ),
					__( 'Monitor mobile performance monthly', 'wpshadow' ),
				),
				'testing_steps' => array(
					__( 'Test mobile: Chrome DevTools → Device toolbar → iPhone 12', 'wpshadow' ),
					__( 'Test desktop: Chrome DevTools → Desktop resolution', 'wpshadow' ),
					__( 'Compare load times in Network tab', 'wpshadow' ),
					__( 'Run Google PageSpeed Insights (mobile and desktop)', 'wpshadow' ),
					__( 'Test on real device: WebPageTest with mobile agent', 'wpshadow' ),
					__( 'Check mobile Core Web Vitals in Search Console', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Measure performance gap
	 *
	 * Performs load time measurements with mobile and desktop user agents
	 * to calculate performance disparity.
	 *
	 * @since  1.6028.1515
	 * @return array|null Performance comparison data or null if failed.
	 */
	private static function measure_performance_gap() {
		$home_url = home_url( '/' );

		// Measure desktop performance.
		$desktop_time = self::measure_with_user_agent(
			$home_url,
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
		);

		// Measure mobile performance.
		$mobile_time = self::measure_with_user_agent(
			$home_url,
			'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'
		);

		if ( is_null( $desktop_time ) || is_null( $mobile_time ) ) {
			return null;
		}

		// Calculate gap.
		$gap_seconds = $mobile_time - $desktop_time;
		$gap_percentage = ( $mobile_time > 0 ) ? round( ( $desktop_time / $mobile_time ) * 100 ) : 100;

		return array(
			'mobile_load_time'  => round( $mobile_time, 2 ),
			'desktop_load_time' => round( $desktop_time, 2 ),
			'gap_seconds'       => round( $gap_seconds, 2 ),
			'gap_percentage'    => $gap_percentage,
		);
	}

	/**
	 * Measure with user agent
	 *
	 * Performs HTTP request with specified user agent and measures response time.
	 *
	 * @since  1.6028.1515
	 * @param  string $url        URL to measure.
	 * @param  string $user_agent User agent string.
	 * @return float|null Load time in seconds or null if failed.
	 */
	private static function measure_with_user_agent( $url, $user_agent ) {
		$start_time = microtime( true );

		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'headers'   => array(
					'User-Agent' => $user_agent,
				),
			)
		);

		$end_time = microtime( true );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		return $end_time - $start_time;
	}
}
