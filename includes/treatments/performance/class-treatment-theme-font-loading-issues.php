<?php
/**
 * Theme Font Loading Treatment
 *
 * Detects performance issues with theme font loading strategies (render-blocking, unoptimized).
 *
 * **What This Check Does:**
 * 1. Identifies fonts loaded from external CDNs
 * 2. Detects render-blocking font loads
 * 3. Checks for font display strategy (swap, block, fallback)\n * 4. Identifies missing preconnect/preload hints\n * 5. Analyzes cumulative font loading time\n * 6. Measures impact on First Contentful Paint\n *
 * **Why This Matters:**\n * Fonts are render-blocking by default. Browser waits for all fonts before showing text. Large font files
 * or slow CDNs add 1-3 seconds before content appears. With `font-display: swap`, text appears instantly
 * with system font, then custom font loads. Perceived speed: 1-3 seconds faster.\n *
 * **Real-World Scenario:**\n * Theme loaded 5 Google Fonts sequentially (render-blocking). Font loading time: 2.8 seconds. Page
 * completely blank until fonts loaded. Visitors waited 2.8 seconds before seeing any text. After
 * implementing: font-display: swap, preconnect to Google, async font loading, text appeared in 0.2
 * seconds. Bounce rate dropped 22%. Cost: 1 hour configuration. Value: $18,000 in recovered conversions.\n *
 * **Business Impact:**\n * - Blank page 1-3 seconds before content appears\n * - Visitors bounce immediately (think page is broken)\n * - Bounce rate increases 20-40%\n * - SEO ranking penalty (Core Web Vitals failure)\n * - Conversion rate drops 25-50%\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Immediate visual improvement\n * - #8 Inspire Confidence: Professional, fast appearance\n * - #10 Talk-About-Worthy: "Content appears instantly now"\n *
 * **Related Checks:**\n * - Theme Asset Loading Optimization (overall asset strategy)\n * - Critical CSS Implementation (above-the-fold content)\n * - First Contentful Paint (content visibility)\n * - Mobile Performance (mobile font impact)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/font-loading-optimization\n * - Video: https://wpshadow.com/training/google-fonts-optimization (6 min)\n * - Advanced: https://wpshadow.com/training/variable-fonts-performance (10 min)\n *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Font Loading Treatment Class
 *
 * Checks for inefficient font loading in theme.
 *
 * @since 0.6093.1200
 */
class Treatment_Theme_Font_Loading_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-font-loading-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Font Loading Issues';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for font loading performance issues';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Font_Loading_Issues' );
	}
}
