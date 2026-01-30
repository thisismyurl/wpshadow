<?php
/**
 * Site Search Functionality Diagnostic
 *
 * Verifies site search working properly to enable
 * visitors to find content.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Site_Search_Functionality Class
 *
 * Verifies site search is functional.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Site_Search_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-search-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Search Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site search is working';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if search not working, null otherwise.
	 */
	public static function check() {
		$search_status = self::check_site_search();

		if ( $search_status['is_working'] ) {
			return null; // Search working
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Site search not returning results or not indexed. Visitor cannot find content = bounces to Google = lost traffic. Verify search enabled and database indexed.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/site-search',
			'family'       => self::$family,
			'meta'         => array(
				'search_enabled' => false,
			),
			'details'      => array(
				'why_site_search_matters'         => array(
					'User Path' => array(
						'1. Google search: Find your site',
						'2. Site search: Find specific content',
						'3. No search: Bounce to competitor',
					),
					'Engagement' => array(
						'Search users: 40% higher conversion',
						'Non-search: Random browsing',
					),
					'Content Discovery' => array(
						'Related products: Cross-sell',
						'Archive content: Always findable',
					),
				),
				'wordpress_native_search'         => array(
					'Default' => array(
						'Searches: Post titles + content',
						'Performance: Slow on large sites (100+ posts)',
						'Accuracy: Limited - full-text search',
					),
					'Limitations' => array(
						'Slow: O(n) scan of all posts',
						'Case-insensitive: Works',
						'Partial words: Not supported by default',
						'Relevance: No ranking by relevance',
					),
				),
				'search_enhancements'             => array(
					'WooCommerce' => array(
						'Included: WooCommerce search',
						'Searches: Product titles, descriptions',
						'Custom: WooCommerce settings',
					),
					'Relevanssi Plugin' => array(
						'Cost: Free + premium',
						'Improvement: Relevance ranking',
						'Performance: Indexed searches (fast)',
					),
					'Algolia' => array(
						'Service: Cloud search (not local)',
						'Cost: $0-999/month',
						'Benefit: Excellent for large catalogs',
					),
					'Elasticsearch' => array(
						'Self-hosted: Complex setup',
						'Performance: Very fast',
						'Use: Enterprise sites only',
					),
				),
				'debugging_search_issues'         => array(
					'Search Widget Missing' => array(
						'Check: Theme has search widget?',
						'Add: Search widget to sidebar',
						'Or: Add search form in menu',
					),
					'Search Disabled' => array(
						'Setting: wp-admin → Settings → Reading',
						'Or: Plugin disabled search',
						'Fix: Re-enable in settings',
					),
					'No Results' => array(
						'Cause: Posts not indexed',
						'Fix: Reindex via plugin (Relevanssi)',
						'Or: Update database indexes',
					),
					'Slow Search' => array(
						'Cause: Large database',
						'Solution: Add index on postmeta',
						'Or: Use plugin for fast search',
					),
				),
				'optimizing_search'               => array(
					'Database Indexes' => array(
						'Table: wp_posts',
						'Indexes: post_title, post_content',
						'Speed improvement: 10-100x',
					),
					'Caching' => array(
						'Cache search results: 1 hour',
						'Reduce: Database queries',
						'Hit rate: High for popular searches',
					),
					'Filters' => array(
						'Allow: Filter by category',
						'Allow: Filter by date',
						'UX: Narrow results to relevant',
					),
				),
			),
		);
	}

	/**
	 * Check site search.
	 *
	 * @since  1.2601.2148
	 * @return array Search functionality status.
	 */
	private static function check_site_search() {
		// Check if search widget is active
		$search_widget_active = is_active_widget( false, false, 'search', false );

		// Try a test search
		$test_results = get_posts( array(
			's'              => 'test',
			'post_type'      => 'post',
			'posts_per_page' => 1,
		) );

		$is_working = ! empty( $test_results ) || $search_widget_active;

		return array(
			'is_working' => $is_working,
		);
	}
}
