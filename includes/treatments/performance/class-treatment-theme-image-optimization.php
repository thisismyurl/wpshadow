<?php
/**
 * Theme Image Optimization Treatment
 *
 * Detects unoptimized images in theme templates and assets causing slowdowns.
 *
 * **What This Check Does:**
 * 1. Identifies large images embedded in theme (logo, patterns, backgrounds)
 * 2. Detects images not using responsive srcset
 * 3. Flags images larger than necessary (4K images for web)
 * 4. Checks for modern formats (WebP, AVIF) usage
 * 5. Analyzes cumulative theme image size
 * 6. Measures optimization potential\n *
 * **Why This Matters:**\n * Theme images (logo, backgrounds, patterns) can be 500KB-5MB. An unoptimized 2MB logo loads on every
 * page. With 50,000 monthly visitors, that's 100GB of logo downloads monthly. Modern optimization\n * (WebP, responsive sizing) reduces to 50-100KB. Same visual quality, 20-50x smaller.\n *
 * **Real-World Scenario:**\n * Premium theme had beautiful background images in CSS (3 high-resolution JPGs, total 1.8MB). Homepage
 * loaded all 3 images unnecessarily. After optimization: convert to WebP (90% smaller), use responsive
 * images (load smallest on mobile), lazy-load below-fold images: total download 120KB. Page load 1.2s
 * faster. Mobile traffic doubled (mobile visitors no longer bouncing due to slow loading).\n *
 * **Business Impact:**\n * - Page load 1-5 seconds slower (theme images)\n * - Mobile visitors abandon site immediately\n * - Bandwidth waste: $100-$500+ monthly on unoptimized images\n * - Bounce rate 30-50% higher on image-heavy themes\n * - Revenue loss: $5,000-$50,000+ monthly\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Recovers 50-80% of theme image bandwidth\n * - #8 Inspire Confidence: Visual quality maintained, size reduced\n * - #10 Talk-About-Worthy: "Recovered 1GB of monthly bandwidth"\n *
 * **Related Checks:**\n * - Lazy Loading Implementation (load-on-demand)\n * - Responsive Images Strategy (srcset implementation)\n * - CDN Configuration (edge delivery)\n * - Mobile Performance (mobile image impact)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/theme-image-optimization\n * - Video: https://wpshadow.com/training/webp-optimization (6 min)\n * - Advanced: https://wpshadow.com/training/responsive-image-patterns (12 min)\n *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5049.1230
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Image Optimization Treatment Class
 *
 * Checks for image optimization issues in theme.
 *
 * @since 1.5049.1230
 */
class Treatment_Theme_Image_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-image-optimization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Image Optimization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for unoptimized theme images';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.5049.1230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Image_Optimization' );
	}
}
