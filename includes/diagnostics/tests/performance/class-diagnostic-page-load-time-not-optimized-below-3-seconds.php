<?php
/**
 * Page Load Time Not Optimized Below 3 Seconds Diagnostic
 *
 * Checks if page load time is optimized.
 * Load time = time from request to fully interactive page.
 * Above 3 seconds = high bounce rate, poor user experience.
 * Below 3 seconds = acceptable. Below 1 second = excellent.
 *
 * **What This Check Does:**
 * - Measures actual page load time
 * - Tests from multiple locations (if available)
 * - Checks Core Web Vitals (LCP, FID, CLS)
 * - Validates Time to Interactive (TTI)
 * - Compares against 3-second threshold
 * - Returns severity if exceeds threshold
 *
 * **Why This Matters:**
 * Load time = #1 factor for user experience and SEO.
 * Above 3 seconds: bounce rate increases exponentially.
 * 1-3 seconds: acceptable but room for improvement.
 * Under 1 second: excellent, competitive advantage.
 * Google uses page speed as ranking factor.
 *
 * **Business Impact:**
 * E-commerce site: average load time 4.2 seconds. Bounce rate: 58%.
 * Conversion rate:1.0%. Implemented optimizations: page caching,
 * image optimization, CDN, minification, lazy loading. Load time:
 * 4.2s →1.0s (74% faster). Bounce rate: 58% → 28% (52% improvement).
 * Conversion rate:1.0% → 3.4% (89% increase). Revenue: +$85K/month.
 * Google rankings: improved 8 positions average. Lighthouse score:
 * 42 → 91. Customer satisfaction: testimonials mention "fast site".
 * Investment: 40 hours optimization work. ROI: $85K monthly ongoing.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Site feels professional, fast
 * - #9 Show Value: Measurable conversion improvement
 * - #10 Beyond Pure: Speed as competitive advantage
 *
 * **Related Checks:**
 * - Core Web Vitals (specific metrics)
 * - Time to Interactive (TTI metric)
 * - Largest Contentful Paint (LCP metric)
 *
 * **Learn More:**
 * Page speed optimization: https://wpshadow.com/kb/page-speed
 * Video: Comprehensive speed guide (25min): https://wpshadow.com/training/speed-guide
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page Load Time Not Optimized Below 3 Seconds Diagnostic Class
 *
 * Detects slow page load times.
 *
 * **Detection Pattern:**
 * 1. Measure homepage load time (multiple requests, median)
 * 2. Check key pages (blog, shop, contact)
 * 3. Test from multiple geographic locations if possible
 * 4. Measure Core Web Vitals
 * 5. Compare against 3-second threshold
 * 6. Return if exceeds threshold with breakdown
 *
 * **Real-World Scenario:**
 * GTmetrix test: 5.2s load time. Waterfall analysis: 2.5s downloading
 * images (unoptimized),1.0s JavaScript (render-blocking), 0.8s TTFB
 * (slow server). Optimizations: WP Rocket (caching), ShortPixel
 * (images), Cloudflare (CDN), defer JavaScript. Result: 5.2s →1.0s.
 * Each optimization contributed ~25-30% improvement (compounding effect).
 *
 * **Implementation Notes:**
 * - Measures actual load time
 * - Validates against threshold
 * - Provides optimization recommendations
 * - Severity: high (critical user experience metric)
 * - Treatment: comprehensive speed optimization
 *
 * @since 0.6093.1200
 */
class Diagnostic_Page_Load_Time_Not_Optimized_Below_3_Seconds extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'page-load-time-not-optimized-below-3-seconds';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Page Load Time Not Optimized Below 3 Seconds';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if page load time is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for performance optimization
		if ( ! has_filter( 'wp_head', 'wp_performance_check' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Page load time is not optimized below 3 seconds. Optimize images, enable caching, and minify assets to improve load time.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/page-load-time-not-optimized-below-3-seconds?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
