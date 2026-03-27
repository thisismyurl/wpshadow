<?php
/**
 * Mobile FID (First Input Delay) Detection
 *
 * Measures lag between user tap/click and browser response on mobile devices.
 *
 * **What This Check Does:**
 * 1. Measures First Input Delay using Performance API
 * 2. Tracks JavaScript execution blocking main thread
 * 3. Identifies heavy scripts running during interaction
 * 4. Calculates typical interaction delay for mobile users
 * 5. Flags poor FID that harms mobile experience
 * 6. Correlates FID with script size and complexity
 *
 * **Why This Matters:**
 * When a user taps a button but has to wait 2-5 seconds for response, they think the site is broken.
 * This happens when JavaScript is executing (parsing large bundles, running expensive computations).
 * The browser can't respond to the tap because it's busy. Mobile users experience this as an
 * unresponsive, laggy site. Google made FID a Core Web Vital because it directly measures
 * interactivity. High FID hurts search rankings and conversions.
 *
 * **Real-World Scenario:**
 * News site with heavy analytics, ad networks, and social media widgets. FID measured at 3.2 seconds.
 * Users complained about tapping links that seemed to not work. Main thread profiling showed
 * Chartbeat analytics, Google Analytics, and Facebook Pixel all parsing/executing simultaneously.
 * After deferring non-critical scripts to interaction-idle, FID dropped to 0.12 seconds.
 * User "unresponsive site" complaints dropped 88%. Time-on-page increased 35%.
 * Cost: 6 hours script optimization. Value: $120,000 in retained traffic and ad revenue.
 *
 * **Business Impact:**
 * - Mobile users perceive site as slow/broken (even if initial load was fast)
 * - Form submissions fail (users give up waiting)
 * - E-commerce: cart abandonment from slow checkout
 * - Google Search penalty (Core Web Vital affecting rankings)
 * - Conversion loss ($1,000-$100,000 for high-traffic sites)
 * - App-like experience impossible
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents invisible responsiveness problems
 * - #9 Show Value: Improves user-perceived performance immediately
 * - #10 Talk-About-Worthy: "Site feels snappy now" is immediately noticed
 *
 * **Related Checks:**
 * - JavaScript Loading Strategy Not Optimized (main thread blocking)
 * - Total Blocking Time Not Minimized (task execution)
 * - Third-Party Scripts Not Deferred (external bloat)
 * - Core Web Vitals Failing (overall performance)
 *
 * **Learn More:**
 * - KB Article: https://wpshadow.com/kb/mobile-fid-detection
 * - Video: https://wpshadow.com/training/fid-optimization (5 min)
 * - Advanced: https://wpshadow.com/training/main-thread-optimization (11 min)
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile FID Detection
 *
 * Measures responsiveness delay for mobile user interactions during page load.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Fid extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-fid-slow';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile FID (First Input Delay)';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Detects when mobile FID exceeds 100ms threshold';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Identifies main thread blocking tasks:
	 * - Good FID: <100ms
	 * - Needs Improvement: 100-300ms
	 * - Poor: >300ms
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Fid' );
	}
}
