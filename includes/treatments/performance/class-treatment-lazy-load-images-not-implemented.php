<?php
/**
 * Lazy Load Images Not Implemented Treatment
 *
 * Checks if lazy loading is implemented.
 * Lazy loading = load images only when visible in viewport.
 * Without = all images load immediately (even offscreen).
 * With lazy loading = offscreen images load when scrolled into view.
 *
 * **What This Check Does:**
 * - Scans for images in content
 * - Checks for loading="lazy" attribute
 * - Validates lazy loading library (if custom)
 * - Tests offscreen image loading behavior
 * - Measures initial page load impact
 * - Returns severity if images not lazy-loaded
 *
 * **Why This Matters:**
 * Blog post with 30 images. All load immediately.
 * User sees 3 images above fold. Downloaded 30.
 * Wasted bandwidth (27 images). Slow initial load.
 * With lazy loading: download 3 initially, others on-demand.
 * Fast initial load. Bandwidth saved.
 *
 * **Business Impact:**
 * Long-form article: 45 images, 18MB total. Initial page load:
 * downloads all 45 images. Mobile users: 25-second load on 3G.
 * Bounce rate: 72% (leave before content loads). Enabled native
 * lazy loading (loading="lazy"). Initial load: 5 images above fold
 * (2MB). Remaining 40 load when scrolled. Load time: 25s → 3.5s
 * (86% faster). Bounce rate: 72% → 18%. Bandwidth saved 70% for
 * users who don't scroll to bottom. Setup: WordPress 5.5+ automatic.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Content loads quickly
 * - #9 Show Value: Massive bandwidth + speed improvement
 * - #10 Beyond Pure: Smart resource loading
 *
 * **Related Checks:**
 * - Lazy Loading Attribute Usage (native implementation)
 * - Image Optimization (complementary)
 * - Offscreen Image Detection (related metric)
 *
 * **Learn More:**
 * Lazy loading: https://wpshadow.com/kb/lazy-loading
 * Video: Native lazy loading (8min): https://wpshadow.com/training/lazy-load
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
 * Lazy Load Images Not Implemented Treatment Class
 *
 * Detects non-lazy-loaded images.
 *
 * **Detection Pattern:**
 * 1. Parse HTML content
 * 2. Find all <img> tags
 * 3. Check for loading="lazy" attribute
 * 4. Check for lazy loading JavaScript library
 * 5. Test actual loading behavior (offscreen images)
 * 6. Return if images load eagerly
 *
 * **Real-World Scenario:**
 * WordPress 5.5+ automatically adds loading="lazy" to content images.
 * Result: <img src="..." loading="lazy">. Browser handles lazy loading
 * natively (zero JavaScript). Older WordPress or custom themes may not.
 * Added manually: images below fold get loading="lazy". Hero image:
 * no lazy (loads immediately). Result: initial page weight reduced 75%.
 *
 * **Implementation Notes:**
 * - Checks loading attribute presence
 * - Validates lazy loading implementation
 * - Tests offscreen loading behavior
 * - Severity: medium (significant bandwidth + speed improvement)
 * - Treatment: enable native lazy loading or plugin
 *
 * @since 1.6093.1200
 */
class Treatment_Lazy_Load_Images_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'lazy-load-images-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Lazy Load Images Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if lazy loading is implemented';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Lazy_Load_Images_Not_Implemented' );
	}
}
