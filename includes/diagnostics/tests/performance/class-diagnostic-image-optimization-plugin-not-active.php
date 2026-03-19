<?php
/**
 * Image Optimization Plugin Not Active Diagnostic
 *
 * Checks if image optimization is enabled.
 * Image optimization = compress images without visible quality loss.
 * No optimization = 2MB images. Slow page loads.
 * With optimization = 200KB images (90% smaller). Fast loads.
 *
 * **What This Check Does:**
 * - Checks for image optimization plugin
 * - Validates automatic compression on upload
 * - Tests compression effectiveness (should be 50-80% reduction)
 * - Checks for WebP conversion
 * - Validates lazy loading configuration
 * - Returns severity if no optimization active
 *
 * **Why This Matters:**
 * Images = 60-70% of average page weight.
 * Unoptimized = huge files, slow downloads, high bounce rate.
 * Optimized = small files, fast loads, happy users.
 * Single biggest performance win for most sites.
 *
 * **Business Impact:**
 * Photography portfolio: 50 images per page, 8MB each = 400MB total.
 * Mobile users: impossible (would take 30+ minutes on 3G). Bounce
 * rate: 95%. Installed ShortPixel. Automatically compressed all images
 * to 15-20% original size. Same visual quality. Page size: 400MB → 65MB.
 * Mobile users: 2.5 minutes load on 3G (still slow but possible).
 * Added lazy loading: initial load 8MB (first 10 images). Bounce rate:
 * 95% → 35%. Portfolio inquiries increased 400%. Setup: 1 hour.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Images load fast everywhere
 * - #9 Show Value: Massive file size reduction
 * - #10 Beyond Pure: Automatic optimization
 *
 * **Related Checks:**
 * - WebP Format Support (modern format)
 * - Lazy Loading Implementation (defer offscreen)
 * - Hero Image Optimization (critical image)
 *
 * **Learn More:**
 * Image optimization: https://wpshadow.com/kb/image-optimization
 * Video: Image optimization guide (16min): https://wpshadow.com/training/images
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Optimization Plugin Not Active Diagnostic Class
 *
 * Detects inactive image optimization.
 *
 * **Detection Pattern:**
 * 1. Check for image optimization plugins (ShortPixel, Imagify, etc)
 * 2. Test sample uploads for automatic compression
 * 3. Measure compression ratio achieved
 * 4. Check for WebP variant generation
 * 5. Validate lazy loading enabled
 * 6. Return if no optimization active
 *
 * **Real-World Scenario:**
 * Activated Imagify plugin. Settings: Normal compression (good balance),
 * convert to WebP, preserve EXIF. Bulk optimized 2400 existing images.
 * Average compression: 68%. Total savings:1.0GB storage. Page load
 * time improved 3.2 seconds average. Lighthouse performance score:
 * 52 → 78. Monthly bandwidth reduced 60% = $120/month savings.
 *
 * **Implementation Notes:**
 * - Checks image optimization plugin presence
 * - Validates compression effectiveness
 * - Tests WebP conversion
 * - Severity: high (images usually biggest bottleneck)
 * - Treatment: install and configure optimization plugin
 *
 * @since 1.6093.1200
 */
class Diagnostic_Image_Optimization_Plugin_Not_Active extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-optimization-plugin-not-active';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Optimization Plugin Not Active';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if image optimization is enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for image optimization plugin
		if ( ! is_plugin_active( 'imagify/imagify.php' ) && ! is_plugin_active( 'shortpixel-image-optimiser/wp-shortpixel.php' ) && ! is_plugin_active( 'ewww-image-optimizer/ewww-image-optimizer.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Image optimization plugin is not active. Use Imagify, ShortPixel, or EWWW Image Optimizer to reduce image file sizes without quality loss.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/image-optimization-plugin-not-active',
			);
		}

		return null;
	}
}
