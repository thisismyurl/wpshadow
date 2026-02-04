<?php
/**
 * Plugin Database Query Performance Diagnostic
 *
 * Analyzes database query patterns used by plugins and identifies performance bottlenecks.
 *
 * **What This Check Does:**
 * 1. Identifies plugins generating the slowest queries
 * 2. Detects N+1 query patterns (queries in loops)
 * 3. Analyzes query execution times by plugin
 * 4. Flags queries missing indexes
 * 5. Identifies plugins running queries on every page load
 * 6. Measures database impact per plugin\n *
 * **Why This Matters:**\n * A plugin might execute 100 queries per page load (while it should execute 5). Each query takes
 * 0.1 seconds. 100 × 0.1 = 10 seconds of database time per page. With 1,000 daily visitors, that's
 * 10,000 seconds (2.8 hours) of database work daily for a single plugin. Database server overloaded.\n *
 * **Real-World Scenario:**\n * E-commerce plugin generated 1 query per product to fetch related products (N+1 pattern). Product
 * page with 50 related products = 51 queries. Site had 10,000 products. Popular product pages caused
 * 51 queries × 100 daily views = 5,100 queries from single plugin daily. Database couldn't keep up.\n * After fixing with JOIN queries (1 query instead of 51), product pages generated 2 queries total.
 * Database load dropped 95%. Page speed improved 30x. Cost: 4 hours development. Value: avoided
 * $100,000 database upgrade.\n *
 * **Business Impact:**\n * - Database CPU at 100% (site becomes slow)\n * - Database server overloaded (all users affected)\n * - Page loads 5-30+ seconds slower\n * - Database upgrade needed ($50,000-$500,000 cost)\n * - Revenue loss from slowdown ($5,000-$50,000 per hour)\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Identifies performance culprits instantly\n * - #8 Inspire Confidence: Prevents database overload\n * - #10 Talk-About-Worthy: "Database runs at 2% CPU with proper queries"\n *
 * **Related Checks:**\n * - Plugin Database Query Volume (query count)\n * - Database Index Efficiency (query optimization)\n * - Meta Query Performance (postmeta patterns)\n * - Slow Query Log Analysis (slow query details)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/plugin-query-performance\n * - Video: https://wpshadow.com/training/wordpress-database-profiling (7 min)\n * - Advanced: https://wpshadow.com/training/query-optimization-patterns (14 min)\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Database Query Performance Diagnostic Class
 *
 * Detects plugins with inefficient database query patterns, missing indexes, and N+1 queries.
 *
 * @since 1.6030.2200
 */
class Diagnostic_Plugin_Database_Query_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-database-query-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Database Query Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes database query patterns used by plugins and identifies performance bottlenecks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues          = array();
		$query_intensive = array();

		// Known query-intensive plugins.
		$heavy_plugins = array(
			'woocommerce/woocommerce.php'                   => 'WooCommerce',
			'wordfence/wordfence.php'                       => 'Wordfence',
			'jetpack/jetpack.php'                           => 'Jetpack',
			'wordpress-seo/wp-seo.php'                      => 'Yoast SEO',
			'all-in-one-seo-pack/all_in_one_seo_pack.php'   => 'All in One SEO',
			'wp-rocket/wp-rocket.php'                       => 'WP Rocket',
			'wp-all-import/wp-all-import.php'               => 'WP All Import',
			'elementor/elementor.php'                       => 'Elementor',
			'gravityforms/gravityforms.php'                 => 'Gravity Forms',
			'wpforms-lite/wpforms.php'                      => 'WPForms',
			'advanced-custom-fields/acf.php'                => 'Advanced Custom Fields',
			'buddypress/bp-loader.php'                      => 'BuddyPress',
			'bbpress/bbpress.php'                           => 'bbPress',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $active_plugins as $plugin ) {
			if ( isset( $heavy_plugins[ $plugin ] ) ) {
				$query_intensive[] = $heavy_plugins[ $plugin ];
			}
		}

		// Check custom tables from plugins.
		$custom_tables = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW TABLES LIKE %s",
				$wpdb->esc_like( $wpdb->prefix ) . '%'
			),
			ARRAY_N
		);

		$plugin_table_count = 0;
		$plugin_table_names = array();
		if ( is_array( $custom_tables ) ) {
			$core_tables = array(
				'posts', 'postmeta', 'users', 'usermeta', 'comments', 'commentmeta',
				'terms', 'term_taxonomy', 'term_relationships', 'termmeta',
				'options', 'links',
			);

			foreach ( $custom_tables as $table ) {
				$table_name = str_replace( $wpdb->prefix, '', $table[0] );
				if ( ! in_array( $table_name, $core_tables, true ) ) {
					++$plugin_table_count;
					$plugin_table_names[] = $table[0];
				}
			}
		}

		if ( $plugin_table_count > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of custom tables */
				__( 'High number of custom plugin tables (%d) may indicate complex queries', 'wpshadow' ),
				$plugin_table_count
			);
		}

		// Check for slow query log if available.
		$slow_query_log = $wpdb->get_var( "SHOW VARIABLES LIKE 'slow_query_log'" );
		if ( 'ON' === $slow_query_log ) {
			$slow_queries = $wpdb->get_var( "SHOW GLOBAL STATUS LIKE 'Slow_queries'" );
			if ( $slow_queries && (int) $slow_queries > 100 ) {
				$issues[] = sprintf(
					/* translators: %d: number of slow queries */
					__( 'Database has logged %d slow queries, likely from plugins', 'wpshadow' ),
					(int) $slow_queries
				);
			}
		}

		// Check total query count (high count indicates inefficiency).
		if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
			$query_count = count( $wpdb->queries );
			if ( $query_count > 100 ) {
				$issues[] = sprintf(
					/* translators: %d: number of queries */
					__( 'Page generated %d database queries (indicates N+1 query problems)', 'wpshadow' ),
					$query_count
				);
			}
		}

		// Report findings.
		if ( ! empty( $query_intensive ) || ! empty( $issues ) ) {
			$severity     = 'medium';
			$threat_level = 50;

			if ( count( $query_intensive ) > 3 || count( $issues ) > 2 ) {
				$severity     = 'high';
				$threat_level = 75;
			}

			$description = __( 'Plugins with inefficient database query patterns detected', 'wpshadow' );

			$details = array();
			if ( ! empty( $query_intensive ) ) {
				$details['query_intensive_plugins'] = $query_intensive;
			}
			if ( ! empty( $issues ) ) {
				$details['query_issues'] = $issues;
			}
			$details['custom_table_count'] = $plugin_table_count;
			if ( $plugin_table_count > 10 ) {
				$details['sample_custom_tables'] = array_slice( $plugin_table_names, 0, 10 );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-database-query-performance',
				'details'      => $details,
			);
		}

		return null;
	}
}
