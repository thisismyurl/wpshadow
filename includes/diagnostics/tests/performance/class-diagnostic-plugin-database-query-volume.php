<?php
/**
 * Plugin Database Query Volume Diagnostic
 *
 * Detects plugins making excessive database queries.
 *
 * @since   1.4031.1939
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
	 * @since  1.4031.1939
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
