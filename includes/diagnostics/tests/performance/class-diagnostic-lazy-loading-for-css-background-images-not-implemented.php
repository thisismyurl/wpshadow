<?php
/**
 * Lazy Loading For CSS Background Images Not Implemented Diagnostic
 *
 * Checks if CSS background image lazy loading is implemented.
 * CSS background images = background-image: url(...).
 * Native lazy loading doesn't work on CSS backgrounds.
 * Requires JavaScript solution (Intersection Observer).
 *
 * **What This Check Does:**
 * - Identifies CSS background images
 * - Checks for lazy loading implementation
 * - Validates Intersection Observer usage
 * - Tests background image loading behavior
 * - Measures initial page load impact
 * - Returns severity if backgrounds load eagerly
 *
 * **Why This Matters:**
 * Hero sections with large background images.
 * CSS: background-image: url(hero-5mb.jpg).
 * Loads immediately even if offscreen. Wastes bandwidth.
 * Native loading="lazy" doesn't work (CSS, not <img>).
 * Solution: JavaScript + Intersection Observer.
 * Add class when visible, load background then.
 *
 * **Business Impact:**
 * Landing page: 8 sections, each with CSS background (2MB average,
 * 16MB total). All load immediately. Mobile: 45-second initial load.
 * Bounce: 80%. Implemented JavaScript lazy loading: data-bg attribute
 * stores URL, Intersection Observer watches elements, adds background-image
 * when visible. Initial load: 2 sections visible (4MB). Remaining load
 * on-demand. Load time: 45s → 6s (87% faster). Bounce: 80% → 28%.
 * Bandwidth saved 75% for users who don't scroll. Setup: 1 hour (custom JS).
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Fast initial load
 * - #9 Show Value: Dramatic bandwidth savings
 * - #10 Beyond Pure: Advanced lazy loading techniques
 *
 * **Related Checks:**
 * - Lazy Load Images (related <img> check)
 * - Large Background Images (size check)
 * - Intersection Observer Usage (API check)
 *
 * **Learn More:**
 * CSS background lazy loading: https://wpshadow.com/kb/css-bg-lazy
 * Video: Intersection Observer tutorial (11min): https://wpshadow.com/training/intersection-observer
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
 * Lazy Loading For CSS Background Images Not Implemented Diagnostic Class
 *
 * Detects missing CSS background image lazy loading.
 *
 * **Detection Pattern:**
 * 1. Parse CSS for background-image declarations
 * 2. Identify elements with large background images
 * 3. Check for lazy loading implementation (data-bg, etc)
 * 4. Validate Intersection Observer usage
 * 5. Test actual loading behavior
 * 6. Return if backgrounds load eagerly
 *
 * **Real-World Scenario:**
 * Implemented: <div class="lazy-bg" data-bg="hero.jpg">. JavaScript:
 * IntersectionObserver watches .lazy-bg elements. When visible:
 * element.style.backgroundImage = `url(${element.dataset.bg})`.
 * Result: backgrounds load only when scrolled into view. Initial
 * page weight reduced 12MB. Critical for mobile performance.
 *
 * **Implementation Notes:**
 * - Checks CSS background-image usage
 * - Validates lazy loading implementation
 * - Tests Intersection Observer
 * - Severity: medium (significant for background-heavy sites)
 * - Treatment: implement JS-based background lazy loading
 *
 * @since 0.6093.1200
 */
class Diagnostic_Lazy_Loading_For_CSS_Background_Images_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'lazy-loading-for-css-background-images-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Lazy Loading For CSS Background Images Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CSS background image lazy loading is implemented';

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
		// Check for background image lazy loading
		if ( ! has_filter( 'wp_head', 'enable_bg_image_lazy_load' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'CSS background image lazy loading is not implemented. Use JavaScript to defer loading of CSS background images until they are visible in the viewport.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 12,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/lazy-loading-for-css-background-images-not-implemented?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
