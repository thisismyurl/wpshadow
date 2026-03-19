<?php
/**
 * Search Performance Diagnostic
 *
 * Checks if product search performs with <500ms response time.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Search Performance Diagnostic Class
 *
 * Verifies that product search returns results quickly and that the
 * search functionality is optimized.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Search_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'search-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Search Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if product search performs with <500ms response time';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the search performance diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if search performance issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for WooCommerce.
		if ( ! function_exists( 'wc' ) || ! class_exists( 'WooCommerce' ) ) {
			$warnings[] = __( 'WooCommerce not active - skipping search performance check', 'wpshadow' );
			return null;
		}

		// Get total product count.
		$product_count = wc_get_products( array(
			'return' => 'ids',
			'limit'  => 1,
			'paginate' => true,
		) );

		$total_products = isset( $product_count->total ) ? $product_count->total : 0;
		$stats['total_products'] = $total_products;

		if ( $total_products === 0 ) {
			$warnings[] = __( 'No products found - cannot test search', 'wpshadow' );
			return null;
		}

		// Test search performance.
		$start_time = microtime( true );

		$search_results = wc_get_products( array(
			's'    => 'test',
			'limit' => 10,
		) );

		$end_time = microtime( true );
		$search_time = ( $end_time - $start_time ) * 1000; // Convert to milliseconds.

		$stats['search_response_time_ms'] = round( $search_time, 2 );

		if ( $search_time > 1000 ) {
			$issues[] = sprintf(
				/* translators: %d: milliseconds */
				__( 'Product search takes %dms (target: <500ms)', 'wpshadow' ),
				intval( $search_time )
			);
		} elseif ( $search_time > 500 ) {
			$warnings[] = sprintf(
				/* translators: %d: milliseconds */
				__( 'Product search takes %dms (target: <500ms)', 'wpshadow' ),
				intval( $search_time )
			);
		}

		// Check for search indexing plugin.
		$search_plugins = array(
			'elasticsearch/elasticsearch.php',
			'elasticpress/elasticpress.php',
			'relevanssi/relevanssi.php',
		);

		$has_search_index = false;
		$search_plugin = null;

		foreach ( $search_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_search_index = true;
				$search_plugin = $plugin;
				break;
			}
		}

		$stats['search_index_plugin'] = $search_plugin ?: 'None';

		if ( ! $has_search_index && $total_products > 1000 ) {
			$warnings[] = __( 'Large product catalog without search indexing - consider using Elasticsearch or similar', 'wpshadow' );
		}

		// Check database indexing.
		global $wpdb;
		$indexes = $wpdb->get_results(
			"SHOW INDEXES FROM {$wpdb->posts} WHERE Column_name = 'post_title' OR Column_name = 'post_content'"
		);

		$has_text_index = ! empty( $indexes );
		$stats['database_indexes'] = $has_text_index;

		if ( ! $has_text_index ) {
			$warnings[] = __( 'Database text indexes not optimized', 'wpshadow' );
		}

		// Check for search filters/facets.
		$has_filters = get_option( 'woocommerce_layered_nav_enabled' );
		$stats['search_filters_enabled'] = boolval( $has_filters );

		if ( ! $has_filters ) {
			$warnings[] = __( 'Product filters not enabled - impacts search experience', 'wpshadow' );
		}

		// Check search caching.
		$search_caching = get_option( 'woocommerce_cache_search_results' );
		$stats['search_result_caching'] = boolval( $search_caching );

		if ( ! $search_caching ) {
			$warnings[] = __( 'Search result caching not enabled', 'wpshadow' );
		}

		// Check for AJAX search.
		$ajax_search = is_plugin_active( 'product-search-woocommerce/product-search.php' ) ||
					   is_plugin_active( 'search-everything/search-everything.php' );

		$stats['ajax_search_enabled'] = $ajax_search;

		if ( ! $ajax_search ) {
			$warnings[] = __( 'AJAX search not enabled - slower search experience', 'wpshadow' );
		}

		// Check search result count.
		$test_search = wc_get_products( array(
			's'     => 'a',
			'limit' => 1,
			'paginate' => true,
		) );

		$result_count = isset( $test_search->total ) ? $test_search->total : 0;
		$stats['typical_search_result_count'] = $result_count;

		if ( $result_count > 10000 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( 'Search often returns large result sets (%d+) - consider better filtering', 'wpshadow' ),
				$result_count
			);
		}

		// Check for search analytics.
		$search_analytics = get_option( 'woocommerce_track_search_queries' );
		$stats['search_analytics'] = boolval( $search_analytics );

		// Check MySQL slow log for search queries.
		$slow_queries = get_option( 'woocommerce_slow_search_queries' );
		$stats['slow_queries_detected'] = ! empty( $slow_queries );

		if ( ! empty( $slow_queries ) ) {
			$warnings[] = __( 'Slow search queries detected - optimize search index', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Search performance has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/search-performance',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Search performance has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/search-performance',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Search performance is good.
	}
}
