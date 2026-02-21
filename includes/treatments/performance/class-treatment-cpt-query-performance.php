<?php
/**
 * CPT Query Performance Treatment
 *
 * Detects slow or inefficient custom post type queries that degrade overall site performance.
 *
 * **What This Check Does:**
 * 1. Measures query performance for custom post type listings
 * 2. Identifies N+1 query patterns in CPT loops
 * 3. Detects missing indexes on CPT-specific columns
 * 4. Checks for inefficient post__in clauses with many IDs
 * 5. Analyzes taxonomy queries for CPT filtering
 * 6. Flags slow CPT archive page queries
 *
 * **Why This Matters:**
 * Custom post types (e-commerce products, events, listings) are often queried inefficiently. A CPT
 * loop that loads 50 items but makes 250 database queries (N+1 pattern) is not uncommon. Each extra
 * query adds 50-200ms. 200 extra queries × 100ms = 20 seconds of wasted database time per page load.
 * With 10,000 daily visits, that's 2.3 hours of wasted database processing per day.\n *
 * **Real-World Scenario:**\n * Real estate site listing 5,000 properties (CPT). Property archive page loaded 20 properties but made
 * 320 database queries (1 to get properties + 15 to get metadata/images/relationships per property).
 * Page took 45 seconds to load. After optimizing to get all data in 5 queries (using get_posts with
 * meta_query consolidation and lazy-loading images), archive loaded in 1.2 seconds. Property inquiries
 * increased 58% because site no longer timed out on archive pages. Cost: 8 hours optimization.
 * Value: $125,000 in additional property leads that quarter.\n *
 * **Business Impact:**\n * - Archive pages timeout (potential customers never see listings)\n * - Admin CPT management slow (admins can't edit/bulk actions)\n * - Site search broken if using CPT search (N+1 multiplies)\n * - Database server overwhelmed (affects all users)\n * - Revenue loss from timeouts ($5,000-$50,000 for e-commerce)\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents cascade failures on high-traffic CPT pages\n * - #9 Show Value: Delivers 10-50x speedup for CPT archives\n * - #10 Talk-About-Worthy: \"Our product pages load instantly\" is huge for e-commerce\n *
 * **Related Checks:**\n * - Meta Query Performance (CPT metadata optimization)\n * - Missing Query Indexes (CPT-specific indexes)\n * - N+1 Query Detection (related pattern)\n * - Taxonomy Query Performance (CPT filtering)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/cpt-query-performance\n * - Video: https://wpshadow.com/training/custom-post-type-queries (7 min)\n * - Advanced: https://wpshadow.com/training/n-plus-one-elimination (12 min)\n *
 * @package    WPShadow\n * @subpackage Treatments\n * @since      1.6030.2148\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Treatments;\n\nuse WPShadow\\Core\\Treatment_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\n/**\n * CPT Query Performance Class\n *\n * Analyzes custom post type query patterns for N+1 queries and missing indexes.
 *
 * @since 1.6030.2148
 */
class Treatment_CPT_Query_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-query-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Query Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measures query performance for custom post type listings';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Tests query performance for CPTs and identifies slow queries
	 * or missing database indexes.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if performance issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_CPT_Query_Performance' );
	}
}
