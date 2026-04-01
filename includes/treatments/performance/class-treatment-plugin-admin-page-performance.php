<?php
/**
 * Plugin Admin Page Performance Treatment
 *
 * Detects plugins slowing down WordPress admin interface through inefficient page loads.
 *
 * **What This Check Does:**
 * 1. Measures admin page load times by plugin
 * 2. Identifies plugins slowing pages by 5+ seconds
 * 3. Detects expensive database queries on admin pages
 * 4. Flags plugins loading unneeded assets admin-wide
 * 5. Analyzes DOM complexity (element count)
 * 6. Measures impact on editor, list tables, dashboard
 *
 * **Why This Matters:**
 * A single slow plugin turns admin work into constant waiting. Editing a post takes 10 seconds instead
 * of 1 second. Bulk actions timeout. List tables never load. Admin becomes unusable. Productivity
 * drops to zero. Site admins get frustrated and disable the plugin, losing its functionality.\n *
 * **Real-World Scenario:**\n * WordPress site had 15 plugins. Admin dashboard took 8 seconds to load. SEO plugin was responsible
 * for 6 of those 8 seconds (analyzing competitor backlinks on every page load). After moving analysis
 * to background job (not page load), dashboard loaded in 0.8 seconds instantly. Editing posts went
 * from 12 seconds to 1 second. Editor became responsive again. Staff productivity increased 60%.
 * Cost: 2 hours plugin configuration. Value: $8,000 in recovered time annually.\n *
 * **Business Impact:**\n * - Admin becomes unusable (staff productivity collapses)\n * - Editing posts slow and frustrating\n * - Bulk operations timeout\n * - List tables fail to load\n * - Backup/import operations hang\n * - Time spent waiting: $100-$500 daily\n * - Staff morale: "This system is broken"\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Restores admin usability instantly\n * - #8 Inspire Confidence: Identifies problematic plugins clearly\n * - #10 Talk-About-Worthy: "Admin loads instantly now" is huge for team morale\n *
 * **Related Checks:**\n * - Plugin Database Query Impact (query-level analysis)\n * - Plugin Asset Loading (unnecessary enqueue detection)\n * - Admin AJAX Performance (AJAX response times)\n * - Front-End Plugin Performance (user-facing slowdowns)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/plugin-admin-performance\n * - Video: https://wpshadow.com/training/debugging-slow-admin (7 min)\n * - Advanced: https://wpshadow.com/training/plugin-profiling (12 min)\n *
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
 * Plugin Admin Page Performance Treatment Class
 *
 * Detects admin pages with excessive load times, large DOM trees, or heavy resource usage.
 *
 * @since 0.6093.1200
 */
class Treatment_Plugin_Admin_Page_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-admin-page-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Admin Page Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes admin page load performance and identifies slow-loading plugin admin pages';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Plugin_Admin_Page_Performance' );
	}
}
