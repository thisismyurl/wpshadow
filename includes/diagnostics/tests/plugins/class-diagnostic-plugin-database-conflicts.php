<?php
/**
 * Plugin Database Conflicts Diagnostic
 *
 * Detects database table collisions and schema conflicts between plugins.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2205
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Database Conflicts Diagnostic Class
 *
 * Identifies table name conflicts, duplicate column names, and schema issues between plugins.
 *
 * @since 1.2601.2205
 */
class Diagnostic_Plugin_Database_Conflicts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-database-conflicts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Database Conflicts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects database table collisions and schema conflicts between plugins';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2205
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$conflicts = array();
		$issues    = array();

		// Get all custom tables.
		$all_tables = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW TABLES LIKE %s",
				$wpdb->esc_like( $wpdb->prefix ) . '%'
			),
			ARRAY_N
		);

		if ( empty( $all_tables ) ) {
			return null;
		}

		$custom_tables = array();
		$core_tables   = array(
			$wpdb->prefix . 'posts',
			$wpdb->prefix . 'postmeta',
			$wpdb->prefix . 'users',
			$wpdb->prefix . 'usermeta',
			$wpdb->prefix . 'comments',
			$wpdb->prefix . 'commentmeta',
			$wpdb->prefix . 'terms',
			$wpdb->prefix . 'term_taxonomy',
			$wpdb->prefix . 'term_relationships',
			$wpdb->prefix . 'termmeta',
			$wpdb->prefix . 'options',
			$wpdb->prefix . 'links',
		);

		foreach ( $all_tables as $table ) {
			if ( ! in_array( $table[0], $core_tables, true ) ) {
				$custom_tables[] = $table[0];
			}
		}

		// Check for similar table names (potential conflicts).
		$table_base_names = array();
		foreach ( $custom_tables as $table ) {
			$base_name = str_replace( $wpdb->prefix, '', $table );
			// Remove common suffixes.
			$base_name = preg_replace( '/_(data|meta|cache|log|temp|queue)$/', '', $base_name );

			if ( ! isset( $table_base_names[ $base_name ] ) ) {
				$table_base_names[ $base_name ] = array();
			}
			$table_base_names[ $base_name ][] = $table;
		}

		$similar_tables = array_filter( $table_base_names, function( $tables ) {
			return count( $tables ) > 1;
		} );

		if ( ! empty( $similar_tables ) ) {
			foreach ( $similar_tables as $base => $tables ) {
				$conflicts[] = sprintf(
					/* translators: 1: base name, 2: number of similar tables */
					__( 'Similar table name pattern "%1$s" (%2$d tables) may indicate conflicts', 'wpshadow' ),
					$base,
					count( $tables )
				);
			}
		}

		// Check for tables with generic names.
		$generic_names = array( 'data', 'cache', 'log', 'temp', 'queue', 'meta', 'options', 'settings' );
		foreach ( $custom_tables as $table ) {
			$table_name = str_replace( $wpdb->prefix, '', $table );
			if ( in_array( $table_name, $generic_names, true ) ) {
				$issues[] = sprintf(
					/* translators: %s: table name */
					__( 'Generic table name "%s" increases conflict risk', 'wpshadow' ),
					$table_name
				);
			}
		}

		// Check for orphaned tables (tables from deleted plugins).
		$known_plugin_tables = array(
			'woocommerce_sessions'         => 'woocommerce/woocommerce.php',
			'wfls_'                        => 'wordfence/wordfence.php',
			'yoast_'                       => 'wordpress-seo/wp-seo.php',
			'icl_'                         => 'sitepress-multilingual-cms/sitepress.php',
			'elementor_'                   => 'elementor/elementor.php',
			'wpforms_'                     => 'wpforms-lite/wpforms.php',
			'gf_'                          => 'gravityforms/gravityforms.php',
			'edd_'                         => 'easy-digital-downloads/easy-digital-downloads.php',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		$orphaned_tables = array();

		foreach ( $custom_tables as $table ) {
			foreach ( $known_plugin_tables as $pattern => $plugin_file ) {
				if ( strpos( $table, $wpdb->prefix . $pattern ) === 0 ) {
					if ( ! in_array( $plugin_file, $active_plugins, true ) ) {
						$orphaned_tables[] = $table;
					}
				}
			}
		}

		if ( ! empty( $orphaned_tables ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned tables */
				__( 'Found %d orphaned database tables from deleted plugins (can cause conflicts)', 'wpshadow' ),
				count( $orphaned_tables )
			);
		}

		// Check table sizes for unusual growth.
		$large_tables = array();
		foreach ( $custom_tables as $table ) {
			$size_query = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT
						ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
						TABLE_ROWS as row_count
					FROM information_schema.TABLES
					WHERE table_schema = %s
					AND table_name = %s",
					DB_NAME,
					$table
				)
			);

			if ( $size_query && $size_query->size_mb > 100 ) {
				$large_tables[] = array(
					'table'     => $table,
					'size_mb'   => $size_query->size_mb,
					'row_count' => $size_query->row_count,
				);
			}
		}

		if ( count( $large_tables ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of large tables */
				__( '%d plugin tables over 100MB (may impact performance and backups)', 'wpshadow' ),
				count( $large_tables )
			);
		}

		// Report findings.
		if ( ! empty( $conflicts ) || ! empty( $issues ) ) {
			$severity     = 'medium';
			$threat_level = 50;

			if ( ! empty( $orphaned_tables ) || count( $large_tables ) > 5 ) {
				$severity     = 'high';
				$threat_level = 70;
			}

			$description = __( 'Database conflicts between plugins detected that may cause data issues', 'wpshadow' );

			$details = array(
				'custom_table_count' => count( $custom_tables ),
			);

			if ( ! empty( $conflicts ) ) {
				$details['conflicts'] = $conflicts;
			}
			if ( ! empty( $issues ) ) {
				$details['issues'] = $issues;
			}
			if ( ! empty( $orphaned_tables ) ) {
				$details['orphaned_tables'] = array_slice( $orphaned_tables, 0, 10 );
			}
			if ( ! empty( $large_tables ) ) {
				$details['large_tables'] = array_slice( $large_tables, 0, 5 );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-database-conflicts',
				'details'      => $details,
			);
		}

		return null;
	}
}
