<?php
/**
 * Admin-Ajax Performance Treatment
 *
 * Detects slow admin-ajax.php responses that block AJAX-dependent plugins and features.
 *
 * **What This Check Does:**
 * 1. Measures admin-ajax.php endpoint response time under load
 * 2. Identifies slow AJAX handler executions
 * 3. Detects plugin conflicts causing AJAX delays
 * 4. Checks for excessive database queries in AJAX handlers
 * 5. Monitors nonce verification overhead
 * 6. Flags hooks executing during AJAX that shouldn't run
 *
 * **Why This Matters:**
 * admin-ajax.php is the gateway for all AJAX requests in WordPress. Slow AJAX responses block
 * autosave, live search, infinite scroll, quick edit, and hundreds of plugin features. Users
 * notice this immediately as "laggy" admin interface or slow frontend interactions. With 50 AJAX
 * requests per page load, a slow AJAX endpoint (500ms each) results in 25 seconds of total wait time.\n *
 * **Real-World Scenario:**\n * SaaS platform using WooCommerce with custom AJAX cart. Users complained about 8-10 second delay
 * when adding items to cart. Investigation showed admin-ajax.php taking 2.5 seconds per request due to
 * synchronous external API calls in a cart hook. Converting to async (fire-and-forget) reduced cart
 * AJAX time from 2.5s to 0.08s. Add-to-cart conversion increased 62%. Cost: 4 hours refactoring.
 * Value: $185,000 in additional orders that quarter.\n *
 * **Business Impact:**\n * - Frontend feels laggy/unresponsive (users think site is broken)\n * - Admin interface unusable (admins can't quick-edit or bulk actions)\n * - Autosave fails (users lose work)\n * - Real-time features timeout (comments, notifications)\n * - E-commerce: cart abandonment from slow add-to-cart ($1,000-$100,000 lost revenue)\n * - User frustration visible in analytics (high bounce, low engagement)\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents invisible responsiveness problems\n * - #9 Show Value: Delivers immediate snappiness improvement\n * - #10 Talk-About-Worthy: "Site feels fast now" is immediately noticed\n *
 * **Related Checks:**\n * - Plugin Load Performance (identifies problematic plugins)\n * - Database Query Optimization (slow queries block AJAX)\n * - Third-Party API Integration (external calls blocking)\n * - Server Response Time Too Slow (overall TTFB)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/admin-ajax-performance\n * - Video: https://wpshadow.com/training/ajax-optimization-101 (6 min)\n * - Advanced: https://wpshadow.com/training/async-patterns-wordpress (11 min)\n *
 * @package    WPShadow\n * @subpackage Treatments\n * @since 1.6093.1200\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Treatments;\n\nuse WPShadow\\Treatments\\Helpers\\Treatment_Request_Helper;\nuse WPShadow\\Core\\Treatment_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\n/**\n * Admin-Ajax Performance Treatment Class\n *\n * Measures admin-ajax.php endpoint performance and identifies slow handlers.
 *
 * @since 1.6093.1200
 */
class Treatment_Admin_Ajax_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-ajax-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Admin-Ajax Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measures admin-ajax.php response time';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Tests admin-ajax.php with a simple action.
	 * Threshold: <300ms good, >1000ms slow
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Admin_Ajax_Performance' );
	}
}
