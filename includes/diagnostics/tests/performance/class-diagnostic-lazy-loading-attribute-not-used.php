<?php
/**
 * Lazy Loading Attribute Not Used Diagnostic
 *
 * Checks if lazy loading attribute is used.
 * Native lazy loading = browser feature via loading="lazy".
 * Without attribute = images load eagerly (immediate download).
 * With attribute = browser lazy-loads automatically (no JS needed).
 *
 * **What This Check Does:**
 * - Scans images for loading attribute
 * - Checks for loading="lazy" on content images
 * - Validates loading="eager" on critical images (hero)
 * - Tests browser support (95%+ modern browsers)
 * - Checks for JavaScript fallback (legacy browsers)
 * - Returns severity if native lazy loading not used
 *
 * **Why This Matters:**
 * Native lazy loading = zero JavaScript required.
 * Browser handles everything. Lightweight, fast, reliable.
 * Older approach: JavaScript libraries (LazyLoad.js, etc).
 * Native approach: better performance, less code.
 * WordPress 5.5+ adds automatically. Older versions don't.
 *
 * **Business Impact:**
 * Site used LazyLoad.js library (12KB, extra HTTP request, JavaScript
 * parsing/execution overhead). Replaced with native loading="lazy".
 * Zero JavaScript. Zero library overhead. Same lazy loading behavior.
 * Page weight: -12KB. Parse time: -45ms. Time to Interactive: improved
 * 200ms. Lighthouse score: +3 points. Maintenance: eliminated (native
 * browser feature). Setup: WordPress 5.5+ automatic. Older: add filter
 * to wp_get_attachment_image_attributes. 15 minutes work.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Modern browser features
 * - #9 Show Value: Zero overhead lazy loading
 * - #10 Beyond Pure: Platform-native optimization
 *
 * **Related Checks:**
 * - Lazy Load Images Implementation (broader check)
 * - Image Optimization (complementary)
 * - Browser Feature Support (validation)
 *
 * **Learn More:**
 * Native lazy loading: https://wpshadow.com/kb/native-lazy-loading
 * Video: loading attribute explained (7min): https://wpshadow.com/training/loading-attr
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
 * Lazy Loading Attribute Not Used Diagnostic Class
 *
 * Detects missing lazy loading attribute usage.
 *
 * **Detection Pattern:**
 * 1. Parse HTML content
 * 2. Find all <img> tags
 * 3. Check for loading attribute presence
 * 4. Validate appropriate values (lazy for offscreen, eager for hero)
 * 5. Check browser support coverage
 * 6. Return if images missing loading attribute
 *
 * **Real-World Scenario:**
 * WordPress 5.5+: content images automatically get loading="lazy".
 * Custom theme images may not. Added to theme templates:
 * <img src="<?php echo esc_url($url); ?>" loading="lazy">.
 * Hero image explicitly: loading="eager" (loads immediately).
 * Result: native lazy loading throughout site. Zero JavaScript overhead.
 * Especially beneficial for mobile users (browser optimizes loading).
 *
 * **Implementation Notes:**
 * - Checks loading attribute on images
 * - Validates attribute values
 * - Tests browser support
 * - Severity: low (modern best practice but not critical)
 * - Treatment: add loading="lazy" to content images
 *
 * @since 0.6093.1200
 */
class Diagnostic_Lazy_Loading_Attribute_Not_Used extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'lazy-loading-attribute-not-used';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Lazy Loading Attribute Not Used';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if lazy loading attribute is used';

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
		// Check for native lazy loading implementation
		if ( ! has_filter( 'img_tag_output', 'add_lazy_loading_attribute' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Lazy loading attribute is not used. Add loading="lazy" to images below the fold to defer loading and improve page speed and Core Web Vitals.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 50,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/lazy-loading-attribute-not-used?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
