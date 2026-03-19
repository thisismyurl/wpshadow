<?php
/**
 * Background Image Optimization Not Performed Treatment
 *
 * Checks if background images are optimized.
 * Background images = CSS backgrounds, hero images, parallax.
 * Unoptimized = 5MB image for 1920px hero. Slow load.
 * Optimized = 300KB WebP with srcset. Fast load.
 *
 * **What This Check Does:**
 * - Scans theme CSS for background-image declarations
 * - Checks image file sizes (should be <500KB)
 * - Validates modern format usage (WebP, AVIF)
 * - Tests responsive image variants exist
 * - Checks lazy loading implementation
 * - Returns severity if optimization missing
 *
 * **Why This Matters:**
 * Background images = often largest assets on page.
 * 5MB hero image = 10+ seconds on mobile 3G.
 * Users see blank space. Bounce immediately.
 * Optimized: 200KB WebP loads in 1 second. Users stay.
 *
 * **Business Impact:**
 * Hero background: 8MB uncompressed PNG. Mobile users (60% traffic)
 * wait 15+ seconds for image. Bounce rate: 85%. Lost $50K/month
 * in mobile conversions. After optimization (250KB WebP): loads
 * in 2 seconds. Bounce rate: 30%. Mobile conversions increase
 * 400%. Revenue gain: $200K/month. ROI: 10 minutes optimization work.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Images load fast on all devices
 * - #9 Show Value: Quantified mobile performance gains
 * - #10 Beyond Pure: Proactive image optimization
 *
 * **Related Checks:**
 * - Image Compression (complementary)
 * - Lazy Loading Implementation (related)
 * - WebP Format Support (modern format)
 *
 * **Learn More:**
 * Background image optimization: https://wpshadow.com/kb/background-images
 * Video: Optimizing hero images (11min): https://wpshadow.com/training/hero-optimization
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
 * Background Image Optimization Not Performed Treatment Class
 *
 * Detects unoptimized background images.
 *
 * **Detection Pattern:**
 * 1. Scan active theme stylesheets
 * 2. Extract background-image URLs
 * 3. Check image file sizes
 * 4. Validate format (WebP/AVIF preferred)
 * 5. Test responsive variants exist
 * 6. Return if large unoptimized images found
 *
 * **Real-World Scenario:**
 * Hero section uses 6MB PNG background. Converted to WebP with
 * image-set() for retina displays. File size: 220KB (96% reduction).
 * Mobile page load time: 12s → 2.5s. Bounce rate improved 55%.
 * Desktop unaffected (was already fast).
 *
 * **Implementation Notes:**
 * - Checks CSS background-image declarations
 * - Validates image sizes and formats
 * - Tests responsive image support
 * - Severity: high (>2MB images), medium (>500KB)
 * - Treatment: convert to WebP, generate responsive variants
 *
 * @since 1.6093.1200
 */
class Treatment_Background_Image_Optimization_Not_Performed extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'background-image-optimization-not-performed';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Background Image Optimization Not Performed';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if background images are optimized';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Background_Image_Optimization_Not_Performed' );
	}
}
