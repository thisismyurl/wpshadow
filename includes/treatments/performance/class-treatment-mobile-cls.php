<?php
/**
 * Mobile CLS (Cumulative Layout Shift) Detection
 *
 * Measures visual instability during page load that causes accidental tap misses on mobile.
 *
 * **What This Check Does:**
 * 1. Measures layout shifts during page load using Layout Shift API
 * 2. Tracks all unexpected element movements (without user input)
 * 3. Identifies which resources cause shifts (images, ads, fonts)
 * 4. Calculates cumulative shift score across viewport
 * 5. Flags poor CLS scores that hurt mobile usability
 * 6. Correlates CLS with user interaction misses
 *
 * **Why This Matters:**
 * When page elements shift during load, users trying to tap a button accidentally tap something else.
 * A user tries to click "Continue Shopping" but an ad loads above it, shifting the button down.
 * User accidentally clicks "Leave Site" instead. This cascades into form abandonment, accidental
 * navigation away, and frustrated users. Google made CLS a Core Web Vital because it directly impacts
 * user experience. High CLS sites rank lower in search results.
 *
 * **Real-World Scenario:**
 * E-commerce site with hero images, ads, and widget loading had CLS of 0.42 (very poor). Users
 * complained about clicking wrong buttons constantly. Tracking showed: images without dimensions
 * caused shifts, lazy-loaded ads appeared mid-page, widgets loaded asynchronously. After fixing
 * (reserving space for images, deferring ads, preloading critical widgets), CLS dropped to 0.08.
 * Accidental form submissions decreased 71%. Mobile conversions increased 43%.
 * Cost: 4 hours of optimization. Value: $78,000 in additional mobile revenue that quarter.
 *
 * **Business Impact:**
 * - Mobile users accidentally trigger wrong actions (form abandonment, bounces)
 * - E-commerce: accidental clicks on ads instead of products (lost sales)
 * - Google Search penalty (Core Web Vital affecting rankings)
 * - User frustration visible in analytics (high bounce rate, low time-on-page)
 * - App-like experience impossible (users feel site is broken)
 * - Conversion loss ($1,000-$50,000 for high-traffic sites)
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents invisible UX problems on mobile
 * - #9 Show Value: Improves measurable UX metric (Core Web Vital)
 * - #10 Talk-About-Worthy: "Site stopped being jittery" is very noticeable
 *
 * **Related Checks:**
 * - First Contentful Paint Not Optimized (speed metric)
 * - Largest Contentful Paint Not Optimized (rendering metric)
 * - Image Dimensions Not Set (causes CLS via layout recalc)
 * - JavaScript Loading Strategy Not Optimized (async loading impacts)
 *
 * **Learn More:**
 * - KB Article: https://wpshadow.com/kb/mobile-cls-detection
 * - Video: https://wpshadow.com/training/core-web-vitals-101 (6 min)
 * - Advanced: https://wpshadow.com/training/layout-stability-optimization (12 min)
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
 * Mobile CLS Detection
 *
 * Measures cumulative layout shifts that cause mobile usability problems and form abandonment.
 *
 * @since 1.602.1430
 */
class Treatment_Mobile_Cls extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-cls-high';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile CLS (Cumulative Layout Shift)';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects when mobile CLS exceeds 0.1 threshold';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Identifies layout shift sources:
	 * - Good CLS: <0.1
	 * - Needs Improvement: 0.1-0.25
	 * - Poor: >0.25
	 *
	 * @since  1.602.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Cls' );
	}
}
