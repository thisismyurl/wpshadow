<?php
/**
 * Plugin Asset Loading Performance Treatment
 *
 * Identifies plugins loading CSS and JavaScript on pages where they're not needed.
 *
 * **What This Check Does:**
 * 1. Lists all plugins enqueuing CSS and JavaScript
 * 2. Identifies assets loaded on all pages unnecessarily
 * 3. Detects plugins without page-specific conditions
 * 4. Measures asset file sizes
 * 5. Flags duplicate CSS/JS from multiple plugins
 * 6. Analyzes cumulative impact on page load\n *
 * **Why This Matters:**\n * A plugin might load CSS on every page (homepage, single posts, archives) even though it's only
 * used on one page type. Every page now loads extra CSS/JS that's not used. Visitor's browser downloads
 * 500KB of CSS it doesn't need. Parsing slows page. With 50,000 daily visitors, that's 25GB of wasted
 * bandwidth daily. Actual cost: $100-$500 monthly in bandwidth charges.\n *
 * **Real-World Scenario:**\n * Appointment booking plugin loaded 250KB of CSS and JS on every page (including homepage).
 * Booking interface only appeared on one page (contact form). Homepage now loaded 250KB of unused code.
 * Page speed dropped from1.0 seconds to 3.8 seconds. Bounce rate increased 45%. After configuring
 * plugin to load only on contact page, homepage returned to1.0 seconds and bounce rate fell 45%.
 * Revenue from form submissions increased 35%. Cost: 1 hour configuration. Value: $12,000 in recovered
 * conversions.\n *
 * **Business Impact:**\n * - Page load 2-5 seconds slower (unused assets)\n * - Bandwidth waste: $100-$500 monthly\n * - Mobile visitors especially impacted (limited connections)\n * - Bounce rate increases 40-50% on slow pages\n * - SEO ranking penalty (Google favors fast pages)\n * - Conversion rate drops 30-50%\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Immediate page speed improvements (2-5x)\n * - #8 Inspire Confidence: Clear visibility into asset usage\n * - #10 Talk-About-Worthy: "Pages load 3x faster now"\n *
 * **Related Checks:**\n * - Plugin Admin Page Performance (admin-side asset load)\n * - Minification Status (asset compression)\n * - Lazy Loading Implementation (defer asset loading)\n * - CDN Configuration (asset distribution)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/plugin-asset-loading\n * - Video: https://wpshadow.com/training/conditional-asset-loading (6 min)\n * - Advanced: https://wpshadow.com/training/dependency-optimization (11 min)\n *
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
 * Treatment_Plugin_Asset_Loading Class
 *
 * Identifies plugins loading CSS/JS on all pages unnecessarily.
 */
class Treatment_Plugin_Asset_Loading extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-asset-loading';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Asset Loading Performance';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects inefficient plugin asset loading patterns';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_Asset_Loading' );
	}
}
