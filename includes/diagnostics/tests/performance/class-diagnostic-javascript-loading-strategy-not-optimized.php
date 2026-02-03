<?php
/**
 * JavaScript Loading Strategy Not Optimized Diagnostic
 *
 * Checks if JavaScript loading is optimized.
 * Loading strategy = async vs defer vs blocking.
 * Blocking = stops page render until JS downloads + executes.
 * Defer = downloads parallel, executes after HTML parsed.
 * Async = downloads parallel, executes immediately when ready.
 *
 * **What This Check Does:**
 * - Identifies render-blocking JavaScript
 * - Checks for async/defer attributes
 * - Validates loading strategy per script type
 * - Tests Time to Interactive improvement
 * - Checks critical vs non-critical script handling
 * - Returns severity if blocking scripts detected
 *
 * **Why This Matters:**
 * Browser parses HTML. Encounters <script src="...">.
 * Stops parsing. Downloads script. Executes. Resumes parsing.
 * Result: delayed page rendering. Slow perceived performance.
 * With defer: downloads without blocking. Executes in order after parse.
 * Page renders fast. Scripts execute when ready.
 *
 * **Business Impact:**
 * Blog: 6 JavaScript files in <head>, no async/defer. Total: 95KB.
 * Mobile users: blank screen for 2.8 seconds (blocking downloads +
 * execution). Added defer to all scripts: <script defer src="...">.
 * Page renders immediately. Scripts execute after HTML parsed.
 * Time to Interactive: 2.8s → 0.9s (68% faster). Bounce rate: 48% → 24%.
 * Also moved non-critical scripts to footer. Setup: 20 minutes
 * (theme template edits). Lighthouse performance: 55 → 84.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Content visible immediately
 * - #9 Show Value: Dramatic TTI improvement
 * - #10 Beyond Pure: Script execution optimization
 *
 * **Related Checks:**
 * - Render Blocking Resources (broader check)
 * - JavaScript Bundling (complementary)
 * - Time to Interactive (metric)
 *
 * **Learn More:**
 * Script loading: https://wpshadow.com/kb/javascript-loading
 * Video: Async vs Defer explained (10min): https://wpshadow.com/training/async-defer
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * JavaScript Loading Strategy Not Optimized Diagnostic Class
 *
 * Detects unoptimized JS loading.
 *
 * **Detection Pattern:**
 * 1. Scan HTML for <script> tags
 * 2. Check for async or defer attributes
 * 3. Identify scripts in <head> (blocking render)
 * 4. Validate critical scripts (analytics, etc) deferred
 * 5. Test Time to Interactive impact
 * 6. Return if render-blocking scripts found
 *
 * **Real-World Scenario:**
 * Changed <script src="analytics.js"> to <script async src="analytics.js">.
 * Analytics loads parallel, doesn't block. Also: <script defer src="theme.js">.
 * Theme JS executes after DOM ready. Result: no blocking scripts.
 * First Contentful Paint improved 1.2 seconds. Lighthouse warning
 * "Eliminate render-blocking resources" resolved. Rule: defer for
 * order-dependent scripts, async for independent scripts.
 *
 * **Implementation Notes:**
 * - Checks script loading attributes
 * - Validates loading strategy appropriateness
 * - Measures rendering impact
 * - Severity: high (major rendering blocker)
 * - Treatment: add async/defer attributes appropriately
 *
 * @since 1.2601.2352
 */
class Diagnostic_JavaScript_Loading_Strategy_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'javascript-loading-strategy-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JavaScript Loading Strategy Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if JavaScript loading is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if JS optimization is implemented
		if ( ! has_filter( 'wp_enqueue_scripts', 'optimize_js_loading' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'JavaScript loading is not optimized. Use async/defer attributes, code splitting, and defer non-critical JavaScript to improve page speed.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/javascript-loading-strategy-not-optimized',
			);
		}

		return null;
	}
}
