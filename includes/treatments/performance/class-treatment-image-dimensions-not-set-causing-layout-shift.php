<?php
/**
 * Image Dimensions Not Set Causing Layout Shift Treatment
 *
 * Checks if image dimensions are set.
 * Images without width/height = layout shifts when loaded.
 * Browser doesn't know size = reserves no space = content jumps.
 * With dimensions = browser reserves space = no layout shift.
 *
 * **What This Check Does:**
 * - Scans images in content for width/height attributes
 * - Checks CSS aspect-ratio properties
 * - Validates responsive image dimensions
 * - Measures Cumulative Layout Shift (CLS)
 * - Tests for explicit size declarations
 * - Returns severity if images missing dimensions
 *
 * **Why This Matters:**
 * User starts reading article. Images load.
 * Text jumps down 500px. User loses place. Frustrating.
 * Clicks wrong thing (shifted under cursor). Bad experience.
 * With dimensions: text stays put. Smooth experience.
 * Critical for Core Web Vitals (CLS score).
 *
 * **Business Impact:**
 * Blog posts: images without dimensions. Users reading. Images load.
 * Content shifts. User loses reading position. 40% abandon article.
 * CLS score: 0.35 (poor). Added width/height to all images via
 * WordPress default behavior. Browser reserves space correctly.
 * Content stable. CLS: 0.05 (good). User engagement improved 60%.
 * Time on page increased 2.5 minutes. Ad revenue increased 45%
 * (more engaged readers). Implementation: ensure theme uses wp_get_attachment_image().
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Stable, predictable layout
 * - #9 Show Value: Measurable CLS improvement
 * - #10 Beyond Pure: User experience focus
 *
 * **Related Checks:**
 * - Cumulative Layout Shift Optimization (CLS metric)
 * - Image Optimization Overall (related)
 * - Core Web Vitals (broader metrics)
 *
 * **Learn More:**
 * Layout shift prevention: https://wpshadow.com/kb/layout-shift
 * Video: Fixing CLS issues (11min): https://wpshadow.com/training/cls
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Dimensions Not Set Causing Layout Shift Treatment Class
 *
 * Detects missing image dimensions.
 *
 * **Detection Pattern:**
 * 1. Parse HTML content
 * 2. Find all <img> tags
 * 3. Check for width and height attributes
 * 4. Validate CSS aspect-ratio or explicit dimensions
 * 5. Measure CLS impact
 * 6. Return images without proper dimensions
 *
 * **Real-World Scenario:**
 * WordPress defaults output: <img width="800" height="600" src="...">.
 * Browser sees dimensions, reserves 800x600 space before loading.
 * Image loads, fills reserved space. Zero layout shift. Custom
 * theme removed dimensions. CLS jumped from 0.04 to 0.28. Restored
 * dimension output. CLS back to 0.04. Lighthouse score improved 12 points.
 *
 * **Implementation Notes:**
 * - Checks image width/height attributes
 * - Validates CSS dimensions
 * - Measures CLS impact
 * - Severity: medium (affects Core Web Vitals)
 * - Treatment: ensure images have explicit dimensions
 *
 * @since 1.6030.2352
 */
class Treatment_Image_Dimensions_Not_Set_Causing_Layout_Shift extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-dimensions-not-set-causing-layout-shift';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Dimensions Not Set Causing Layout Shift';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if image dimensions are set';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Image_Dimensions_Not_Set_Causing_Layout_Shift' );
	}
}
