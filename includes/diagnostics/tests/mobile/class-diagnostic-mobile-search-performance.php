<?php
/**
 * Mobile Search Performance Diagnostic
 *
 * Validates that WordPress search functionality performs well on mobile
 * with fast response times and proper result presentation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Mobile
 * @since      1.2602.1230
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Search Performance Diagnostic Class
 *
 * Checks search functionality for mobile-specific performance issues including
 * query speed, result presentation, and mobile optimization.
 *
 * @since 1.2602.1230
 */
class Diagnostic_Mobile_Search_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-search-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Search Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that WordPress search performs well on mobile with fast response times';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2602.1230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Test search performance.
		$search_test = self::test_search_performance();
		if ( ! empty( $search_test['issues'] ) ) {
			$issues = array_merge( $issues, $search_test['issues'] );
		}

		// Check for search optimization plugins.
		$optimization_check = self::check_search_optimization();
		if ( ! empty( $optimization_check['issues'] ) ) {
			$issues = array_merge( $issues, $optimization_check['issues'] );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$issue_count    = count( $issues );
		$threat_level   = min( 70, 50 + ( $issue_count * 5 ) );
		$severity       = $threat_level >= 65 ? 'medium' : 'low';
		$auto_fixable   = false;

		$description = sprintf(
			/* translators: %d: number of search performance issues */
			__( 'Found %d mobile search performance issue(s). 43%% of mobile users abandon slow searches (>3 seconds). Search users convert 2-3x higher than browsers - poor search directly impacts revenue.', 'wpshadow' ),
			$issue_count
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => $auto_fixable,
			'kb_link'      => 'https://wpshadow.com/kb/mobile-search-performance',
			'details'      => array(
				'issue_count'   => $issue_count,
				'issues'        => $issues,
				'why_important' => __(
					'Mobile search performance is critical for user experience:
					
					Search Statistics:
					• 50% of mobile users use site search (Nielsen Norman)
					• Search users convert 2-3x higher than browsers
					• 43% abandon if search takes >3 seconds
					• 80% won\'t return after bad search experience
					• 30% of e-commerce traffic uses search
					
					Mobile-Specific Challenges:
					• Slower network speeds (3G/4G)
					• Limited processing power
					• Users expect instant results
					• Screen size limits result presentation
					• Typing is slower on mobile
					
					Default WordPress Search Issues:
					• No relevance ranking (chronological only)
					• Searches only post_title and post_content
					• Doesn\'t search custom fields or taxonomies
					• No typo tolerance
					• No search analytics
					• Slow on large sites (>1000 posts)
					
					Performance Impact:
					• Default WP search: 0.5-2 seconds (small sites)
					• Default WP search: 2-5+ seconds (large sites)
					• Optimized search: <300ms (any size)
					• Every 100ms delay = 1% conversion drop (Amazon)',
					'wpshadow'
				),
				'how_to_fix'    => __(
					'Optimize search for mobile performance:
					
					Quick Wins (No Plugins):
					1. Add search results limit:
					   add_filter( "pre_get_posts", function( $query ) {
					       if ( $query->is_search && ! is_admin() ) {
					           $query->set( "posts_per_page", 20 );
					       }
					   });
					
					2. Exclude attachment pages from search:
					   add_filter( "pre_get_posts", function( $query ) {
					       if ( $query->is_search && ! is_admin() ) {
					           $query->set( "post_type", ["post", "page"] );
					       }
					   });
					
					Search Optimization Plugins (Recommended):
					
					1. Relevanssi (Free + Premium)
					   • Better relevance ranking
					   • Fuzzy matching (typo tolerance)
					   • Custom field search
					   • Fast AJAX instant search
					   • WordPress.org: relevanssi
					
					2. SearchWP (Premium)
					   • Advanced relevance control
					   • Custom field search
					   • PDF/document search
					   • Fast performance
					   • Site: searchwp.com
					
					3. Algolia Search (Free + Premium)
					   • External search service (very fast)
					   • Typo-tolerant
					   • Instant results as you type
					   • Scales to millions of records
					   • WordPress.org: wp-search-with-algolia
					
					4. FacetWP (Premium)
					   • Advanced filtering + search
					   • Great for e-commerce
					   • Fast faceted search
					   • Site: facetwp.com
					
					Mobile-Specific Optimizations:
					• Enable AJAX instant search (results as you type)
					• Show loading indicator
					• Limit initial results to 10-20
					• Use "Load More" instead of pagination
					• Highlight search terms in results
					• Show result count
					• Provide "Did you mean?" suggestions
					• Cache popular searches
					
					WooCommerce Search:
					• Use product search plugins (YITH, Jetpack)
					• Enable SKU search
					• Search product categories/tags
					• Show product images in results',
					'wpshadow'
				),
			),
		);
	}

	/**
	 * Test search performance.
	 *
	 * @since  1.2602.1230
	 * @return array Test results and issues.
	 */
	private static function test_search_performance() {
		$issues = array();

		// Perform test search query.
		$test_query = 'test';
		$start_time = microtime( true );

		$search_query = new \WP_Query(
			array(
				's'              => $test_query,
				'posts_per_page' => 20,
			)
		);

		$search_time = ( microtime( true ) - $start_time ) * 1000; // Convert to ms.

		// Check search time.
		if ( $search_time > 1000 ) {
			$issues[] = array(
				'issue_type'  => 'slow_search',
				'severity'    => 'high',
				'search_time' => round( $search_time, 2 ),
				'description' => sprintf( 'Search query took %.2fms (should be <300ms)', $search_time ),
			);
		} elseif ( $search_time > 500 ) {
			$issues[] = array(
				'issue_type'  => 'moderate_search_speed',
				'severity'    => 'medium',
				'search_time' => round( $search_time, 2 ),
				'description' => sprintf( 'Search query took %.2fms (acceptable but could be faster)', $search_time ),
			);
		}

		// Check if too many posts are being searched.
		$post_count = wp_count_posts( 'post' );
		$total_posts = $post_count->publish;

		if ( $total_posts > 5000 && $search_time > 500 ) {
			$issues[] = array(
				'issue_type'  => 'large_dataset',
				'severity'    => 'medium',
				'post_count'  => $total_posts,
				'description' => sprintf( 'Searching %d posts without optimization plugin', $total_posts ),
			);
		}

		return array( 'issues' => $issues );
	}

	/**
	 * Check for search optimization plugins.
	 *
	 * @since  1.2602.1230
	 * @return array Check results.
	 */
	private static function check_search_optimization() {
		$issues         = array();
		$active_plugins = get_option( 'active_plugins', array() );

		// List of known search plugins.
		$search_plugins = array(
			'relevanssi'                  => 'Relevanssi',
			'searchwp'                    => 'SearchWP',
			'wp-search-with-algolia'      => 'Algolia Search',
			'ajax-search-lite'            => 'Ajax Search Lite',
			'ivory-search'                => 'Ivory Search',
		);

		$has_search_plugin = false;
		foreach ( $search_plugins as $plugin_slug => $plugin_name ) {
			foreach ( $active_plugins as $active_plugin ) {
				if ( strpos( $active_plugin, $plugin_slug ) !== false ) {
					$has_search_plugin = true;
					break 2;
				}
			}
		}

		// Check post count.
		$post_count  = wp_count_posts( 'post' );
		$total_posts = $post_count->publish;

		if ( ! $has_search_plugin && $total_posts > 500 ) {
			$issues[] = array(
				'issue_type'  => 'no_search_optimization',
				'severity'    => 'medium',
				'description' => sprintf(
					'Site has %d posts but no search optimization plugin detected',
					$total_posts
				),
				'recommendation' => 'Consider installing Relevanssi or SearchWP for better performance',
			);
		}

		return array( 'issues' => $issues );
	}
}
