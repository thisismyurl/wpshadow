<?php
/**
 * Theme Frontend Performance Treatment
 *
 * Analyzes overall theme performance and loading speed characteristics.
 *
 * **What This Check Does:**
 * 1. Measures theme page load time (theme + plugins)
 * 2. Analyzes server response time (TTFB)
 * 3. Measures time to first paint and first contentful paint
 * 4. Identifies theme-specific bottlenecks
 * 5. Compares theme performance against benchmarks
 * 6. Flags performance regressions\n *
 * **Why This Matters:**\n * A slow theme affects every page on site. If theme adds 2 seconds to every page, multiply by 100,000
 * monthly visitors = 200,000 seconds (55+ hours) of wasted visitor time monthly. Revenue impact: $5,000+
 * monthly from bounces and abandoned carts.\n *
 * **Real-World Scenario:**\n * WooCommerce store replaced slow theme (page load 4.2s) with lightweight theme (page load1.0s).
 * Exact same plugins, exact same content. Only theme changed. Bounce rate dropped from 45% to 28%
 * (38% improvement). Conversion rate improved 22%. Monthly revenue increased $15,000. Theme cost $69.
 * ROI: 217x in one month.\n *
 * **Business Impact:**\n * - Page load 2-5+ seconds slower (theme bloat)\n * - Bounce rate 30-50% higher on slow themes\n * - Conversion rate 20-40% lower\n * - Revenue loss: $5,000-$100,000+ monthly\n * - Scaling costs higher (need more servers to handle slow theme)\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Direct correlation to revenue\n * - #8 Inspire Confidence: Identifies problem clearly\n * - #10 Talk-About-Worthy: "Theme change doubled our conversions"\n *
 * **Related Checks:**\n * - Theme Asset Loading Optimization (asset performance)\n * - Theme Database Queries (query performance)\n * - Core Web Vitals (user experience metrics)\n * - Plugin Frontend Performance Impact (plugin comparison)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/theme-performance-comparison\n * - Video: https://wpshadow.com/training/lightweight-theme-selection (7 min)\n * - Advanced: https://wpshadow.com/training/theme-architecture-performance (13 min)\n *
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
 * Theme Frontend Performance Treatment Class
 *
 * Checks theme for performance issues affecting page load times.
 *
 * @since 0.6093.1200
 */
class Treatment_Theme_Frontend_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-frontend-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Frontend Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes theme frontend loading performance';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Frontend_Performance' );
	}
}
