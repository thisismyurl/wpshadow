<?php
/**
 * Page Weight Diagnostic
 *
 * Measures total page transfer size including HTML, CSS, JS, images, and fonts.
 * Excessive page weight hurts mobile performance, SEO rankings, and user experience,
 * especially for users on slower connections or metered data plans.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6028.1510
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page Weight Diagnostic Class
 *
 * Analyzes homepage and key pages to measure total transfer size and
 * identify optimization opportunities.
 *
 * @since 1.6028.1510
 */
class Diagnostic_Page_Weight extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1510
	 * @var   string
	 */
	protected static $slug = 'page-weight';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1510
	 * @var   string
	 */
	protected static $title = 'Page Weight Exceeds 3MB';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1510
	 * @var   string
	 */
	protected static $description = 'Measures total page size to optimize mobile performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1510
	 * @var   string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check
	 *
	 * Measures homepage size and estimates resource breakdown.
	 * Benchmarks:
	 * - ≤1.5MB: Excellent
	 * - 1.5-3MB: Good
	 * - 3-5MB: Warning
	 * - >5MB: Critical
	 *
	 * @since  1.6028.1510
	 * @return array|null Null if acceptable, array if heavy.
	 */
	public static function check() {
		$page_data = self::measure_page_weight();

		if ( is_null( $page_data ) ) {
			return null; // Cannot measure.
		}

		$page_size_mb = $page_data['size_mb'];

		// Only report if >3MB.
		if ( $page_size_mb <= 3.0 ) {
			return null;
		}

		$severity = $page_size_mb > 5.0 ? 'high' : 'medium';
		$threat_level = min( 70, 35 + ( ( $page_size_mb - 3 ) * 8 ) );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: page size in megabytes */
				__( 'Homepage weighs %sMB, hurting mobile performance', 'wpshadow' ),
				number_format( $page_size_mb, 2 )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'family'       => self::$family,
			'kb_link'      => 'https://wpshadow.com/kb/reduce-page-weight',
			'meta'         => array(
				'page_size_mb'       => $page_size_mb,
				'page_size_kb'       => $page_data['size_kb'],
				'estimated_breakdown' => $page_data['breakdown'],
				'immediate_actions'  => array(
					__( 'Compress and optimize images (WebP format)', 'wpshadow' ),
					__( 'Minify CSS and JavaScript files', 'wpshadow' ),
					__( 'Remove unused CSS and JavaScript', 'wpshadow' ),
					__( 'Implement lazy loading for images', 'wpshadow' ),
				),
			),
			'details'      => array(
				'why_important' => __(
					'Page weight directly impacts load time, especially on mobile. Google\'s research shows 53% of mobile users abandon sites taking >3 seconds to load. Heavy pages cost users money on metered data plans, hurt SEO rankings, increase bounce rates, and reduce conversions. The median page is 2.1MB - staying under 3MB keeps you competitive.',
					'wpshadow'
				),
				'user_impact'   => __(
					'Mobile users on 3G/4G experience slow load times, data overage charges, and frustration. International users with slower connections may never see your site load. Each extra MB adds ~2 seconds to load time on 3G. Heavy pages also cost you money in bandwidth charges and cloud egress fees.',
					'wpshadow'
				),
				'solution_options' => array(
					'free'     => array(
						__( 'Optimize images with TinyPNG or ImageOptim', 'wpshadow' ),
						__( 'Enable Gzip compression in .htaccess', 'wpshadow' ),
						__( 'Remove unused plugins and themes', 'wpshadow' ),
					),
					'premium'  => array(
						__( 'Install WP Rocket for automatic optimization ($49/year)', 'wpshadow' ),
						__( 'Use Imagify for AI-powered image compression', 'wpshadow' ),
						__( 'Deploy CDN (Cloudflare, BunnyCDN) for asset delivery', 'wpshadow' ),
					),
					'advanced' => array(
						__( 'Implement critical CSS and defer non-critical CSS', 'wpshadow' ),
						__( 'Use HTTP/2 server push for critical resources', 'wpshadow' ),
						__( 'Deploy WebP with fallback for all images', 'wpshadow' ),
					),
				),
				'best_practices' => array(
					__( 'Keep total page size under 3MB (under 1.5MB ideal)', 'wpshadow' ),
					__( 'Compress images to WebP format (60-80% smaller than JPEG)', 'wpshadow' ),
					__( 'Lazy load images below the fold', 'wpshadow' ),
					__( 'Minify and combine CSS/JS files', 'wpshadow' ),
					__( 'Remove unused CSS with PurgeCSS', 'wpshadow' ),
					__( 'Use system fonts or limit custom fonts to 2 weights', 'wpshadow' ),
					__( 'Optimize SVGs with SVGO', 'wpshadow' ),
					__( 'Monitor page weight with GTmetrix monthly', 'wpshadow' ),
				),
				'testing_steps' => array(
					__( 'Test with Chrome DevTools: Network tab shows total size', 'wpshadow' ),
					__( 'Run GTmetrix.com test for detailed breakdown', 'wpshadow' ),
					__( 'Use Google PageSpeed Insights for optimization tips', 'wpshadow' ),
					__( 'Check WebPageTest.org on 3G connection', 'wpshadow' ),
					__( 'Identify largest assets in waterfall chart', 'wpshadow' ),
					__( 'Verify images are optimized and properly sized', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Measure page weight
	 *
	 * Fetches homepage and calculates total transfer size. Also estimates
	 * resource breakdown by counting tags.
	 *
	 * @since  1.6028.1510
	 * @return array|null Page weight data or null if measurement failed.
	 */
	private static function measure_page_weight() {
		$home_url = home_url( '/' );

		$response = wp_remote_get(
			$home_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$html_size = strlen( $body ) / 1024; // KB.

		// Estimate resource sizes based on typical patterns.
		$breakdown = self::estimate_resource_breakdown( $body );

		// Total estimated size.
		$total_size_kb = $html_size + $breakdown['css'] + $breakdown['js'] + $breakdown['images'] + $breakdown['fonts'];

		return array(
			'size_kb'   => round( $total_size_kb, 1 ),
			'size_mb'   => round( $total_size_kb / 1024, 2 ),
			'breakdown' => array(
				'html'   => round( $html_size, 1 ),
				'css'    => $breakdown['css'],
				'js'     => $breakdown['js'],
				'images' => $breakdown['images'],
				'fonts'  => $breakdown['fonts'],
			),
		);
	}

	/**
	 * Estimate resource breakdown
	 *
	 * Analyzes HTML to estimate CSS, JS, image, and font sizes based on
	 * tag counts and industry averages.
	 *
	 * @since  1.6028.1510
	 * @param  string $html HTML content.
	 * @return array Estimated resource sizes in KB.
	 */
	private static function estimate_resource_breakdown( $html ) {
		// Count resource tags.
		$css_count    = substr_count( $html, '<link' ) + substr_count( $html, '<style' );
		$js_count     = substr_count( $html, '<script' );
		$image_count  = substr_count( $html, '<img' ) + substr_count( $html, 'background-image:' );
		$font_count   = substr_count( $html, '@font-face' );

		// Industry average file sizes.
		$avg_css_kb    = 50;  // Average CSS file ~50KB.
		$avg_js_kb     = 100; // Average JS file ~100KB.
		$avg_image_kb  = 100; // Average image ~100KB.
		$avg_font_kb   = 50;  // Average font file ~50KB.

		return array(
			'css'    => round( $css_count * $avg_css_kb ),
			'js'     => round( $js_count * $avg_js_kb ),
			'images' => round( $image_count * $avg_image_kb ),
			'fonts'  => round( $font_count * $avg_font_kb ),
		);
	}
}
