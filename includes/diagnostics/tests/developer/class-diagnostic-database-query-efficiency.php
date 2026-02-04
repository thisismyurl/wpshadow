<?php
/**
 * Database Query Efficiency Diagnostic
 *
 * Checks for N+1 query problems and inefficient database queries.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Query Efficiency Diagnostic Class
 *
 * Verifies that database queries are efficient and identifies
 * N+1 query problems and other inefficiencies.
 *
 * @since 1.6035.1300
 */
class Diagnostic_Database_Query_Efficiency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-query-efficiency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Query Efficiency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for N+1 query problems and inefficient database queries';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the database query efficiency diagnostic check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if efficiency issues detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Use Query Monitor if available for detailed analysis.
		if ( class_exists( 'QM_DB' ) ) {
			// Query Monitor is active, enable detailed tracking.
			$stats['has_query_monitor'] = true;
		}

		// Check for slow queries in the slow_log (if available).
		$slow_query_table = $wpdb->prefix . 'query_log';
		$slow_queries_table_exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM information_schema.TABLES 
				WHERE table_schema = %s AND table_name = %s",
				DB_NAME,
				$slow_query_table
			)
		);

		// Simulate a test to count queries.
		$query_count_start = $wpdb->num_queries ?? 0;

		// Get all posts (simulating common query scenario).
		$test_posts = get_posts( array(
			'posts_per_page' => 5,
			'post_type'      => 'post',
		) );

		$query_count_after_posts = $wpdb->num_queries ?? 0;
		$queries_for_posts = $query_count_after_posts - $query_count_start;

		$stats['queries_for_posts_list'] = $queries_for_posts;

		// For each post, getting postmeta is an N+1 query problem if not using get_post_meta properly.
		if ( ! empty( $test_posts ) ) {
			$query_count_before_meta = $wpdb->num_queries ?? 0;

			foreach ( $test_posts as $post ) {
				// Get postmeta for each post (this is N+1 if not using cache).
				$meta = get_post_meta( $post->ID );
			}

			$query_count_after_meta = $wpdb->num_queries ?? 0;
			$queries_for_meta = $query_count_after_meta - $query_count_before_meta;

			$stats['queries_for_postmeta'] = $queries_for_meta;

			// N+1 query rule: if you fetched 5 posts and made 5 postmeta queries, that's likely N+1.
			if ( $queries_for_meta === count( $test_posts ) ) {
				$issues[] = sprintf(
					/* translators: %d: number of queries */
					__( 'Possible N+1 query pattern detected: %d separate postmeta queries', 'wpshadow' ),
					$queries_for_meta
				);
			}
		}

		// Check for Query Monitor or similar debugging plugins.
		$debug_plugins = array(
			'query-monitor/query-monitor.php',
			'p3-profiler/p3-profiler.php',
		);

		$has_debug_plugin = false;
		foreach ( $debug_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_debug_plugin = true;
				break;
			}
		}

		if ( ! $has_debug_plugin ) {
			$warnings[] = __( 'No query debugging plugin installed - consider Query Monitor for development', 'wpshadow' );
		}

		// Check if wp_cache is being used.
		if ( ! wp_using_ext_object_cache() ) {
			$warnings[] = __( 'Object cache not enabled - queries not cached', 'wpshadow' );
		} else {
			$stats['object_cache_enabled'] = true;
		}

		// Check theme/plugin files for common inefficiency patterns.
		$theme = wp_get_theme();
		$theme_dir = $theme->get_stylesheet_directory();
		$template_files = glob( $theme_dir . '/*.php' );
		$inefficient_patterns = 0;

		foreach ( array_slice( $template_files, 0, 5 ) as $file ) {
			$content = file_get_contents( $file );

			// Look for loop + query patterns (N+1 indicator).
			if ( preg_match( '/foreach\s*\(\s*.*?get_posts/', $content ) ) {
				$inefficient_patterns++;
			}

			// Look for get_post_meta in loops without batch operations.
			if ( preg_match( '/foreach\s*\(.*?\)\s*\{[\s\S]{1,500}?get_post_meta/', $content ) ) {
				$inefficient_patterns++;
			}

			// Look for WP_Query in loops.
			if ( preg_match( '/foreach\s*\(.*?\)\s*\{[\s\S]{1,500}?new\s+WP_Query/', $content ) ) {
				$inefficient_patterns++;
			}
		}

		if ( $inefficient_patterns > 2 ) {
			$issues[] = sprintf(
				/* translators: %d: number of patterns */
				__( '%d inefficient query patterns found in theme files', 'wpshadow' ),
				$inefficient_patterns
			);
		}

		// Check for proper use of caching.
		$cache_plugin_active = false;
		$cache_plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
		);

		foreach ( $cache_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$cache_plugin_active = true;
				break;
			}
		}

		if ( ! $cache_plugin_active ) {
			$warnings[] = __( 'No page cache plugin active - consider caching for production', 'wpshadow' );
		}

		// Check MySQL version for performance.
		$mysql_version = $wpdb->db_version();
		$stats['mysql_version'] = $mysql_version;

		// Recommend MySQL 8.0+ for better query optimization.
		if ( version_compare( $mysql_version, '5.7', '<' ) ) {
			$warnings[] = sprintf(
				/* translators: %s: MySQL version */
				__( 'MySQL version %s is outdated - upgrade to 8.0+ for better query optimization', 'wpshadow' ),
				$mysql_version
			);
		}

		// Check total number of queries on frontend (if available).
		if ( ! is_admin() && isset( $wpdb->num_queries ) ) {
			$total_queries = $wpdb->num_queries;
			$stats['total_frontend_queries'] = $total_queries;

			if ( $total_queries > 100 ) {
				$issues[] = sprintf(
					/* translators: %d: number of queries */
					__( 'High query count on frontend: %d queries detected', 'wpshadow' ),
					$total_queries
				);
			} elseif ( $total_queries > 50 ) {
				$warnings[] = sprintf(
					/* translators: %d: number of queries */
					__( 'Moderate query count: %d queries detected', 'wpshadow' ),
					$total_queries
				);
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Query efficiency has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-query-efficiency',
				'context'      => array(
					'stats'               => $stats,
					'has_debug_plugin'    => $has_debug_plugin,
					'cache_plugin_active' => $cache_plugin_active,
					'issues'              => $issues,
					'warnings'            => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Query efficiency has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-query-efficiency',
				'context'      => array(
					'stats'               => $stats,
					'has_debug_plugin'    => $has_debug_plugin,
					'cache_plugin_active' => $cache_plugin_active,
					'warnings'            => $warnings,
				),
			);
		}

		return null; // Query efficiency is good.
	}
}
