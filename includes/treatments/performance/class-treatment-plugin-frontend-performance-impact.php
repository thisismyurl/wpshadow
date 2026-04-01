<?php
/**
 * Plugin Frontend Performance Impact Treatment
 *
 * Measures how much plugins are slowing down the front-end website for visitors.
 *
 * **What This Check Does:**
 * 1. Measures page load time with each plugin active
 * 2. Calculates per-plugin performance impact
 * 3. Identifies plugins adding 1+ second to page load
 * 4. Flags plugins degrading Core Web Vitals
 * 5. Analyzes mobile vs desktop impact
 * 6. Prioritizes optimization by revenue impact\n *
 * **Why This Matters:**\n * Visitors are impatient. Page loads 1 second slower = 7% bounce rate increase. Page loads 3 seconds
 * slower = 40% bounce rate increase. A plugin slowing site by1.0 seconds = losing 20%+ of potential
 * revenue per visit. With 100,000 monthly visitors and $2 average revenue per visit, that's $4,000
 * monthly loss from a single slow plugin.\n *
 * **Real-World Scenario:**\n * E-commerce site with 50,000 monthly visitors. Affiliate plugin tracked affiliate clicks (poorly
 * optimized) and added 2.3 seconds to each page load. Bounce rate increased from 28% to 35% (competitors
 * saw 32%). Lost 7% of traffic = 3,500 visitors × $2 = $7,000 monthly revenue loss. After plugin\n * optimization (2 hours work), page load dropped 2.3 seconds and bounce rate returned to 28%. Revenue
 * recovered completely. Cost: 2 hours. Value: $84,000 annually.\n *
 * **Business Impact:**\n * - Bounce rate increases 7-40% (slow pages lose visitors immediately)\n * - Conversion rate drops 20-50% (visitors don't wait)\n * - Mobile visitors especially impacted\n * - Revenue loss: $1,000-$100,000+ monthly\n * - SEO ranking penalty (Google favors fast sites)\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Directly ties to revenue impact\n * - #8 Inspire Confidence: Identifies revenue-draining plugins\n * - #10 Talk-About-Worthy: "Every plugin must earn its performance cost"\n *
 * **Related Checks:**\n * - Plugin Asset Loading (asset impact)\n * - Front-End Core Web Vitals (visitor experience metrics)\n * - Server Response Time (backend performance)\n * - Mobile Performance (mobile-specific slowdowns)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/plugin-frontend-impact\n * - Video: https://wpshadow.com/training/measuring-plugin-impact (6 min)\n * - Advanced: https://wpshadow.com/training/performance-budget-allocation (11 min)\n *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Plugin_Frontend_Performance_Impact Class
 *
 * Detects plugins that significantly impact frontend performance.
 */
class Treatment_Plugin_Frontend_Performance_Impact extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-frontend-performance-impact';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Frontend Performance Impact';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects plugins that negatively impact frontend page load times';

	/**
	 * Treatment family
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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_Frontend_Performance_Impact' );
	}
}
