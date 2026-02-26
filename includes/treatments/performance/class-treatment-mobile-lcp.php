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
 * @subpackage Treatments\Performance
 * @since      1.602.1430
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile LCP Detection
 *
 * Measures render time of the largest visible content element, directly impacting bounce rates.
 *
 * @since 1.602.1430
 */
class Treatment_Mobile_Lcp extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-lcp-slow';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile LCP (Largest Contentful Paint)';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects when mobile LCP exceeds 2.5 second threshold';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Measures LCP performance:
	 * - Good: <2.5s
	 * - Needs Improvement: 2.5-4.0s
	 * - Poor: >4.0s
	 *
	 * @since  1.602.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Lcp' );
	}
}
