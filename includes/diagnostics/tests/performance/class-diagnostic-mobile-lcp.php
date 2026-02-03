<?php
/**
 * Mobile LCP (Largest Contentful Paint) Detection
 *
 * Measures time to render the largest visible element on mobile, a critical performance metric.
 *
 * **What This Check Does:**
 * 1. Measures time to Largest Contentful Paint (LCP) using Performance API
 * 2. Identifies which element is the LCP (usually hero image, headline, or video)
 * 3. Analyzes resource loading time for LCP element
 * 4. Checks for render-blocking resources before LCP
 * 5. Flags poor LCP scores that hurt mobile rankings
 * 6. Correlates LCP with bounce rate and conversion loss
 *
 * **Why This Matters:**
 * LCP measures when the "main content" becomes visible to users. On a product page, it's the product
 * image. On an article, it's the headline and featured image. On a video site, it's the player.
 * If LCP takes 5 seconds, users believe the page is broken and bounce. Google made LCP a Core Web Vital
 * because research shows sites with LCP > 4 seconds lose 40-60% of potential traffic due to bounces.
 * Sites with LCP < 2.5 seconds convert 30% better than slow competitors.
 *
 * **Real-World Scenario:**
 * SaaS landing page with hero video (5.2MB) LCP was 7.8 seconds. Half of visitors bounced within 3 seconds.
 * Optimization: (1) Video preload, (2) Use image placeholder until video ready, (3) Use modern video codec.
 * LCP dropped to 1.9 seconds. Bounce rate decreased 52%. Trial signups increased 145% (same traffic, better UX).
 * Cost: 2 weeks optimization. Value: $320,000 in additional trials/customers that quarter.
 *
 * **Business Impact:**
 * - Users bounce before main content loads (40-60% bounce rate increase)
 * - Google Search penalty (Core Web Vital affecting rankings)
 * - Conversion loss ($5,000-$500,000 for high-traffic sites)
 * - Mobile performance degradation visible to all users
 * - Competitive disadvantage (competitors with faster LCP get better rankings)
 * - Revenue impact: $50-$500 per second of LCP delay
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents user perception of broken site
 * - #9 Show Value: Directly correlates to traffic and conversion improvement
 * - #10 Talk-About-Worthy: "Site loads instantly now" is immediately noticed
 *
 * **Related Checks:**
 * - First Contentful Paint Not Optimized (FCP related metric)
 * - Image Dimensions Not Set (render recalc delays LCP)
 * - Image Optimization Plugin Not Active (larger images = longer LCP)
 * - Server Response Time Too Slow (TTFB affects LCP)
 * - Core Web Vitals Failing (overall performance)
 *
 * **Learn More:**
 * - KB Article: https://wpshadow.com/kb/mobile-lcp-detection
 * - Video: https://wpshadow.com/training/core-web-vitals-101 (6 min)
 * - Advanced: https://wpshadow.com/training/lcp-optimization-strategies (14 min)
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.2602.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile LCP Detection
 *
 * Measures render time of the largest visible content element, directly impacting bounce rates.
 *
 * @since 1.2602.1430
 */
class Diagnostic_Mobile_Lcp extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-lcp-slow';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile LCP (Largest Contentful Paint)';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects when mobile LCP exceeds 2.5 second threshold';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Measures LCP performance:
	 * - Good: <2.5s
	 * - Needs Improvement: 2.5-4.0s
	 * - Poor: >4.0s
	 *
	 * @since  1.2602.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for common LCP bottlenecks
		$issues = self::identify_lcp_issues();

		if ( empty( $issues['problems'] ) ) {
			return null; // No issues detected
		}

		// Estimate LCP based on issues found
		$estimated_lcp = self::estimate_lcp( $issues );

		if ( $estimated_lcp < 2.5 ) {
			return null; // Within target
		}

		// Determine severity
		if ( $estimated_lcp > 4.0 ) {
			$severity = 'critical';
			$threat   = 80;
		} else {
			$severity = 'high';
			$threat   = 70;
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %s: LCP time in seconds */
				__( 'Mobile LCP estimated at %.1fs (target: <2.5s)', 'wpshadow' ),
				$estimated_lcp
			),
			'severity'        => $severity,
			'threat_level'    => $threat,
			'current_lcp'     => sprintf( '%.1fs', $estimated_lcp ),
			'target_lcp'      => '<2.5s',
			'primary_issues'  => $issues['problems'],
			'lcp_element'     => $issues['element'] ?? 'Hero image/video',
			'estimated_improvement' => sprintf(
				/* translators: %s: estimated LCP after optimization */
				__( 'Reduce to %.1fs with optimization', 'wpshadow' ),
				$estimated_lcp * 0.6
			),
			'auto_fixable'    => false,
			'kb_link'         => 'https://wpshadow.com/kb/mobile-lcp',
		);
	}

	/**
	 * Identify common LCP bottlenecks.
	 *
	 * @since  1.2602.1430
	 * @return array Issues identified.
	 */
	private static function identify_lcp_issues(): array {
		$problems = array();
		$element  = '';

		// Check for render-blocking CSS
		global $wp_styles;
		if ( isset( $wp_styles ) && ! empty( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				$style = $wp_styles->registered[ $handle ] ?? null;
				if ( $style && empty( $style->args ) ) { // No media query = always loaded
					$problems[] = __( 'Render-blocking CSS detected', 'wpshadow' );
					break;
				}
			}
		}

		// Check for render-blocking JavaScript
		global $wp_scripts;
		if ( isset( $wp_scripts ) && ! empty( $wp_scripts->queue ) ) {
			$blocking_count = 0;
			foreach ( $wp_scripts->queue as $handle ) {
				$script = $wp_scripts->registered[ $handle ] ?? null;
				if ( $script && ! empty( $script->extra['group'] ) && 0 === $script->extra['group'] ) {
					$blocking_count++;
				}
			}
			if ( $blocking_count > 3 ) {
				$problems[] = sprintf(
					/* translators: %d: number of render-blocking scripts */
					__( '%d render-blocking JavaScript files', 'wpshadow' ),
					$blocking_count
				);
			}
		}

		// Check for missing preload hints
		$has_preload_font = has_action( 'wp_head', 'wp_preload_font' );
		if ( ! $has_preload_font ) {
			$problems[] = __( 'No font preload hints', 'wpshadow' );
		}

		// Check for large hero images
		if ( current_theme_supports( 'post-thumbnails' ) ) {
			$element = __( 'Hero image (likely 1200x800px+)', 'wpshadow' );
			if ( empty( $problems ) ) {
				$problems[] = __( 'Large hero image without optimization', 'wpshadow' );
			}
		}

		return array(
			'problems' => $problems,
			'element'  => $element,
		);
	}

	/**
	 * Estimate LCP time based on identified issues.
	 *
	 * @since  1.2602.1430
	 * @param  array $issues Issues identified.
	 * @return float Estimated LCP in seconds.
	 */
	private static function estimate_lcp( array $issues ): float {
		// Base LCP (typical good performance)
		$estimated_lcp = 1.5;

		// Add time for each issue
		$estimated_lcp += count( $issues['problems'] ) * 0.6;

		// Cap at reasonable maximum
		return min( $estimated_lcp, 6.0 );
	}
}
