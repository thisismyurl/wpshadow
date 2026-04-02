<?php
/**
 * Inline Critical CSS Not Optimized Treatment
 *
 * Checks if critical CSS is inlined.
 * Critical CSS = styles for above-the-fold content.
 * Without inline = wait for style.css download before rendering.
 * With inline = render immediately, load full CSS async.
 *
 * **What This Check Does:**
 * - Identifies critical above-the-fold CSS
 * - Checks if critical styles inlined in <head>
 * - Validates full CSS loaded asynchronously
 * - Tests First Contentful Paint improvement
 * - Checks for render-blocking CSS elimination
 * - Returns severity if critical CSS not inlined
 *
 * **Why This Matters:**
 * Browser downloads HTML. Sees <link rel="stylesheet">.
 * Stops rendering. Downloads CSS. Waits. Then renders.
 * Result: blank screen while CSS downloads. Bad experience.
 * With critical CSS inlined: renders above-fold immediately.
 * Full CSS loads in background. Fast perceived performance.
 *
 * **Business Impact:**
 * Landing page: 120KB style.css, 3G users wait 2.5 seconds to see
 * anything (render-blocking). Extracted critical CSS (above-fold:
 * header, hero, CTA = 8KB). Inlined in <head>. Loaded full CSS with
 * loadCSS.js (async). Result: First Contentful Paint: 2.5s → 0.6s
 * (75% faster). Users see content immediately. Bounce rate: 55% → 28%.
 * Conversion rate improved 45%. Lighthouse performance: 58 → 89.
 * Setup: Critical plugin or manual extraction, 1-2 hours.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Content appears instantly
 * - #9 Show Value: Dramatic FCP improvement
 * - #10 Beyond Pure: Advanced rendering optimization
 *
 * **Related Checks:**
 * - Render Blocking Resources (related)
 * - First Contentful Paint Optimization (metric)
 * - CSS Minification (complementary)
 *
 * **Learn More:**
 * Critical CSS: https://wpshadow.com/kb/critical-css
 * Video: Critical rendering path (14min): https://wpshadow.com/training/critical-render
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline Critical CSS Not Optimized Treatment Class
 *
 * Detects missing critical CSS inlining.
 *
 * **Detection Pattern:**
 * 1. Analyze above-the-fold content (1300px viewport)
 * 2. Extract CSS rules for above-fold elements
 * 3. Check if critical CSS inlined in <head>
 * 4. Validate full CSS loaded async (loadCSS, media="print")
 * 5. Measure render-blocking CSS impact
 * 6. Return if CSS blocking initial render
 *
 * **Real-World Scenario:**
 * Used Critical plugin (WordPress). Generated critical CSS for homepage,
 * blog archive, single post. Inlined in <head>: <style>/* critical */</style>.
 * Full CSS: <link rel="preload" as="style" onload="this.rel='stylesheet'">.
 * Result: content renders before full CSS loads. FCP improved1.0 seconds.
 * Mobile users especially benefited (slow connections). PageSpeed score
 * increased 28 points.
 *
 * **Implementation Notes:**
 * - Checks for inlined critical CSS
 * - Validates async full CSS loading
 * - Measures FCP improvement
 * - Severity: medium (significant rendering improvement)
 * - Treatment: extract and inline critical CSS
 *
 * @since 1.6093.1200
 */
class Treatment_Inline_Critical_CSS_Not_Optimized extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'inline-critical-css-not-optimized';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Inline Critical CSS Not Optimized';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if critical CSS is inlined';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Inline_Critical_CSS_Not_Optimized' );
	}
}
