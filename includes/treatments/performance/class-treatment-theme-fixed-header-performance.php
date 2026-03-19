<?php
/**
 * Theme Fixed Header Performance Treatment
 *
 * Detects if fixed/sticky header elements are impacting performance negatively.
 *
 * **What This Check Does:**
 * 1. Detects fixed or sticky header elements
 * 2. Analyzes performance impact on scrolling
 * 3. Checks for reflow/repaint issues
 * 4. Measures layout shift during scroll
 * 5. Identifies expensive CSS animations
 * 6. Checks for JavaScript scroll listeners\n *
 * **Why This Matters:**\n * Fixed headers are trendy but problematic: every scroll triggers recalculation of fixed elements.
 * Heavy fixed headers (with shadows, complex CSS) cause constant repainting, consuming 20-50% of CPU
 * on scroll. Visitors get stuttering, jank, and poor experience. Mobile especially impacted.\n *
 * **Real-World Scenario:**\n * Theme had complex fixed header (gradient background, shadow, blur effect). On scroll, browser had to
 * recalculate and repaint header every frame (60fps = 60 times per second). With complex CSS, only
 * 30fps achievable. Scroll felt janky and stuttery. After simplifying fixed header (solid background,
 * no effects), 60fps smooth. On mobile, before: 15fps (terrible), after: 50fps (smooth).\n *
 * **Business Impact:**\n * - Scroll stutter/jank (poor user experience)\n * - Mobile performance especially impacted\n * - CPU usage 20-50% just for scroll animations\n * - Visitors avoid site (feel broken)\n * - Bounce rate increases 15-30%\n * - Conversion rate drops (site feels unresponsive)\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Smooth, responsive feeling\n * - #9 Show Value: Perceivable improvement in feel\n * - #10 Talk-About-Worthy: "Smooth as butter" scrolling\n *
 * **Related Checks:**\n * - Mobile Performance (mobile scroll metrics)\n * - Mobile FID (responsiveness on mobile)\n * - Core Web Vitals (user experience metrics)\n * - Theme Animation Performance (effect complexity)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/fixed-header-optimization\n * - Video: https://wpshadow.com/training/css-animation-performance (6 min)\n * - Advanced: https://wpshadow.com/training/intersection-observer-patterns (11 min)\n *
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

class Treatment_Theme_Fixed_Header_Performance extends Treatment_Base {
	protected static $slug = 'theme-fixed-header-performance';
	protected static $title = 'Theme Fixed Header Performance';
	protected static $description = 'Checks if fixed header/sticky elements impact performance';
	protected static $family = 'performance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Fixed_Header_Performance' );
	}
}
