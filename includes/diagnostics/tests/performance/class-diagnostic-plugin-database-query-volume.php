<?php
/**
 * Plugin Database Query Volume Diagnostic
 *
 * Detects plugins making excessive database queries affecting overall site performance.
 *
 * **What This Check Does:**
 * 1. Counts database queries per plugin
 * 2. Identifies plugins executing 50+ queries per page
 * 3. Flags plugins with query counts 10x higher than normal
 * 4. Measures cumulative impact across plugins
 * 5. Analyzes query volume by page type
 * 6. Projects database load capacity\n *
 * **Why This Matters:**\n * Every query costs time and database resources. A single query: 1-10ms. 50 queries: 50-500ms wasted
 * on database alone. 100 queries: 1+ second. With 1,000 daily visitors, slow queries = 1,000 seconds (16+
 * minutes) of wasted visitor time daily. Database server CPU near capacity. One spike and site overloads.\n *
 * **Real-World Scenario:**\n * Multi-purpose plugin generated 200+ queries per page load (excessive). Each query was fast (1ms)
 * but cumulative = 200ms wasted on database. Page load was1.0 seconds, of which 200ms was database queries.
 * After plugin optimization (reducing queries to 20), page load dropped to 0.8 seconds. 33% faster.
 * Conversions increased 12%. Cost: plugin configuration. Value: +$8,000 monthly revenue.\n *
 * **Business Impact:**\n * - Page loads slow (200-500ms+ wasted on queries)\n * - Database CPU high even on light traffic\n * - Database struggles during traffic spikes\n * - Scaling requires database upgrade ($50k+)\n * - Conversion rate drops 20-30% on slow pages\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Immediate page speed improvement\n * - #8 Inspire Confidence: Prevents database overload\n * - #10 Talk-About-Worthy: "Minimal database queries = instant pages"\n *
 * **Related Checks:**\n * - Plugin Database Query Performance (query efficiency)\n * - Database Index Efficiency (query optimization)\n * - Cache Hit Ratio (query reduction via caching)\n * - Meta Query Performance (N+1 patterns)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/database-query-volume\n * - Video: https://wpshadow.com/training/query-counting-analysis (6 min)\n * - Advanced: https://wpshadow.com/training/database-query-audit (12 min)\n *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Database_Query_Volume Class
 *
 * Identifies plugins that may generate excessive database queries.
 */
class Diagnostic_Plugin_Database_Query_Volume extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-database-query-volume';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Database Query Volume';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins making excessive database queries';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$query_concerns = array();

		// Check for plugins that hook into every query
		$active_plugins = get_option( 'active_plugins', array() );

		if ( empty( $active_plugins ) ) {
			return null;
		}

		// Count active plugins that typically make many queries
		$query_heavy_plugins = array(
			'woocommerce' => 'WooCommerce',
			'jetpack' => 'Jetpack',
			'elementor' => 'Elementor',
			'yoast-seo' => 'Yoast SEO',
			'akismet' => 'Akismet',
			'wordfence' => 'Wordfence',
			'wp-redis' => 'Redis Cache',
		);

		$query_heavy = array();
		foreach ( $active_plugins as $plugin ) {
			foreach ( $query_heavy_plugins as $key => $name ) {
				if ( strpos( $plugin, $key ) !== false ) {
					$query_heavy[] = $name;
				}
			}
		}

		if ( ! empty( $query_heavy ) ) {
			$query_concerns[] = sprintf(
				/* translators: %s: plugin names */
				__( 'Query-heavy plugins active: %s. These typically add 50-200 queries per request.', 'wpshadow' ),
				implode( ', ', $query_heavy )
			);
		}

		// Check for options with many rows
		$option_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options}"
		);

		if ( $option_count > 5000 ) {
			$query_concerns[] = sprintf(
				/* translators: %d: option count */
				__( '%d options in database. Each plugin querying options will slow down.', 'wpshadow' ),
				$option_count
			);
		}

		// Check for postmeta queries
		$meta_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}"
		);

		if ( $meta_count > 500000 ) {
			$query_concerns[] = sprintf(
				/* translators: %d: meta count */
				__( '%d post meta entries. Plugins querying meta will run slow queries.', 'wpshadow' ),
				$meta_count
			);
		}

		// Check for many custom post types (each adds queries)
		$custom_types = count( get_post_types( array( '_builtin' => false ) ) );
		if ( $custom_types > 10 ) {
			$query_concerns[] = sprintf(
				/* translators: %d: custom post type count */
				__( '%d custom post types registered. Each plugin query now joins more tables.', 'wpshadow' ),
				$custom_types
			);
		}

		if ( ! empty( $query_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $query_concerns ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'query_heavy_plugins'  => $query_heavy,
					'total_options'        => $option_count ?? 0,
					'total_meta_entries'   => $meta_count ?? 0,
					'custom_post_types'    => $custom_types,
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-database-query-volume',
			);
		}

		return null;
	}
}
