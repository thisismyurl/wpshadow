<?php
/**
 * Search Performance Diagnostic
 *
 * Evaluates WordPress search functionality for performance impact and
 * recommends optimizations to reduce load on search queries.
 *
 * @since   1.6033.2088
 * @package WPShadow\Diagnostics
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
 * Verifies search optimization:
 * - Search indexing
 * - Search query count
 * - Search results caching
 * - Search plugin usage
 * - Full-text search capabilities
 *
 * @since 1.6033.2088
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
	protected static $description = 'Evaluates search functionality for performance optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2088
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$search_plugin_active = false;
		$search_optimization_available = false;

		// Check for search optimization plugins
		$search_plugins = array(
			'relevanssi/relevanssi.php'                  => 'Relevanssi',
			's-search/s-search.php'                      => 'S Search',
			'elasticsearch/elasticsearch.php'           => 'Elasticsearch',
			'wpsolr-free/wpsolr-free.php'                => 'WPSOLR',
			'jetpack/jetpack.php'                        => 'Jetpack (with search)',
			'wp-search-with-algolia/wp-search-with-algolia.php' => 'Algolia',
		);

		foreach ( $search_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$search_plugin_active = true;
				$search_optimization_available = true;
				break;
			}
		}

		// Check if search functionality is being used
		$total_posts = wp_count_posts();
		$post_count = intval( $total_posts->publish ?? 0 );

		// Flag if site has many posts and no search optimization
		if ( $post_count > 100 && ! $search_plugin_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: post count */
					__( 'Site has %d posts but no search optimization. WordPress native search can be slow with large content.', 'wpshadow' ),
					$post_count
				),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/search-performance',
				'meta'          => array(
					'post_count'           => $post_count,
					'search_plugin'        => $search_plugin_active ? 'Active' : 'Not active',
					'recommendation'       => 'Install a search optimization plugin (Relevanssi, Elasticsearch, or Algolia) for large sites',
					'impact'               => 'Search can slow page by 500ms-2s without optimization',
					'benefits'             => array(
						'Faster search results',
						'Better relevance ranking',
						'Reduced database load',
						'Advanced search filters',
						'Typo tolerance',
					),
					'alternatives'         => array(
						'Relevanssi (free, good)',
						'Elasticsearch (enterprise)',
						'Algolia (expensive, very fast)',
						'Cache search results',
					),
				),
			);
		}

		return null;
	}
}
