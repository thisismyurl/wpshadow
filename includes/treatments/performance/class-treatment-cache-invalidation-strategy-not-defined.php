<?php
/**
 * Cache Invalidation Strategy Not Defined Treatment
 *
 * Checks if cache invalidation strategy is defined.
 * Cache invalidation = when to clear cached content.
 * No strategy = users see old content OR cache constantly cleared.
 * Clear strategy = cache cleared only when content changes.
 *
 * **What This Check Does:**
 * - Checks for documented cache invalidation rules
 * - Validates cache clearing on post updates
 * - Tests selective cache invalidation (not full flush)
 * - Checks cache key naming strategy
 * - Validates cache warming after invalidation
 * - Returns severity if strategy undefined
 *
 * **Why This Matters:**
 * No strategy = either stale content (users angry) OR
 * constant cache clearing (site slow). Both bad.
 * Good strategy = clear only affected pages. Users see
 * fresh content. Site stays fast.
 *
 * **Business Impact:**
 * Site uses full cache flush on ANY content change. Update one
 * post = entire cache cleared. Next 1000 page views regenerate
 * cache. Server load spikes. Site crashes. Lost $10K in sales.
 * With selective invalidation: update post = clear only that post's
 * cache + homepage. 2 pages regenerated. Server load normal.
 * Site stable. Zero downtime. Zero lost revenue.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Content updates work reliably
 * - #9 Show Value: Performance maintained during updates
 * - #10 Beyond Pure: Intelligent cache management
 *
 * **Related Checks:**
 * - Object Cache Configuration (cache layer)
 * - Page Cache Implementation (full-page cache)
 * - CDN Cache Management (edge cache)
 *
 * **Learn More:**
 * Cache invalidation: https://wpshadow.com/kb/cache-invalidation
 * Video: Smart cache strategies (15min): https://wpshadow.com/training/cache-strategy
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cache Invalidation Strategy Not Defined Treatment Class
 *
 * Detects missing cache invalidation strategy.
 *
 * **Detection Pattern:**
 * 1. Check for cache plugin configuration
 * 2. Scan for wp_cache_delete/flush calls
 * 3. Test invalidation on post save
 * 4. Validate selective vs full flush
 * 5. Check cache warming after invalidation
 * 6. Return if no clear strategy implemented
 *
 * **Real-World Scenario:**
 * Cache invalidation hooks: save_post clears post cache + home.
 * Update category = clear category archive + posts in category.
 * Update menu = clear all pages with menu. Surgical invalidation.
 * Result: 98% cache hit rate maintained even during frequent updates.
 * Old method (full flush): cache hit rate dropped to 20% after updates.
 *
 * **Implementation Notes:**
 * - Checks cache invalidation hooks
 * - Validates selective invalidation logic
 * - Tests cache warming patterns
 * - Severity: medium (cache efficiency issue)
 * - Treatment: implement selective cache invalidation hooks
 *
 * @since 1.6030.2352
 */
class Treatment_Cache_Invalidation_Strategy_Not_Defined extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'cache-invalidation-strategy-not-defined';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Cache Invalidation Strategy Not Defined';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cache invalidation strategy is defined';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Cache_Invalidation_Strategy_Not_Defined' );
	}
}
