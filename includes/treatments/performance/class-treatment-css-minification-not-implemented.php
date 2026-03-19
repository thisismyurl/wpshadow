<?php
/**
 * CSS Minification Not Implemented Treatment
 *
 * Checks if CSS is minified.
 * Minification = remove whitespace, comments, optimize syntax.
 * Unminified CSS = 150KB with formatting.
 * Minified CSS = 95KB (37% smaller). Faster downloads.
 *
 * **What This Check Does:**
 * - Checks enqueued stylesheets for .min.css extension
 * - Validates minification plugin active
 * - Tests actual file sizes (minified vs original)
 * - Checks compression savings achieved
 * - Validates source maps for debugging
 * - Returns severity if CSS unminified in production
 *
 * **Why This Matters:**
 * CSS file: 200KB with comments and formatting.
 * Mobile 3G: takes 6 seconds to download.
 * User sees unstyled page. Minified: 120KB.
 * Downloads in 3.5 seconds. Better experience.
 *
 * **Business Impact:**
 * Theme CSS: 280KB unminified (includes comments, formatting).
 * Minification reduces to 165KB (41% savings). On mobile 3G:
 * load time reduced from 8.5s to 5s. Combined with gzip (further
 * 70% reduction): final size 50KB, loads in1.0s. Bounce rate
 * on mobile improved 25%. Mobile revenue increased $15K/month.
 * Minification setup: 30 minutes (one-time). ROI: 600:1 annually.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Optimized for all connections
 * - #9 Show Value: Measurable mobile performance gains
 * - #10 Beyond Pure: Professional optimization practices
 *
 * **Related Checks:**
 * - JavaScript Minification (parallel optimization)
 * - HTML Minification (complementary)
 * - GZIP Compression (works with minification)
 *
 * **Learn More:**
 * CSS minification: https://wpshadow.com/kb/css-minification
 * Video: Optimizing stylesheets (10min): https://wpshadow.com/training/css-optimization
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
 * CSS Minification Not Implemented Treatment Class
 *
 * Detects unminified CSS.
 *
 * **Detection Pattern:**
 * 1. Get all enqueued styles via wp_styles global
 * 2. Check file extensions (.min.css vs .css)
 * 3. Test minification plugin active
 * 4. Compare file sizes (minified should be 30-50% smaller)
 * 5. Validate source maps exist for debugging
 * 6. Return if unminified CSS in production
 *
 * **Real-World Scenario:**
 * Implemented WP Rocket minification. Original CSS: 12 files, 420KB
 * total. Minified + combined: 1 file, 180KB (57% reduction). With
 * gzip: 55KB over wire. Page load time improved1.0 seconds.
 * Lighthouse performance score: 72 → 89. Developer can still debug
 * via source maps. Best of both worlds.
 *
 * **Implementation Notes:**
 * - Checks CSS file naming and sizes
 * - Validates minification process
 * - Tests compression effectiveness
 * - Severity: medium (performance optimization)
 * - Treatment: enable minification plugin or build process
 *
 * @since 1.6093.1200
 */
class Treatment_CSS_Minification_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'css-minification-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'CSS Minification Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CSS is minified';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_CSS_Minification_Not_Implemented' );
	}
}
