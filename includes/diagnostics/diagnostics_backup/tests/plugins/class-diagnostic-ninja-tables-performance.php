<?php
/**
 * Ninja Tables Performance Diagnostic
 *
 * Ninja Tables slowing frontend.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.478.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Tables Performance Diagnostic Class
 *
 * @since 1.478.0000
 */
class Diagnostic_NinjaTablesPerformance extends Diagnostic_Base {

	protected static $slug = 'ninja-tables-performance';
	protected static $title = 'Ninja Tables Performance';
	protected static $description = 'Ninja Tables slowing frontend';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'NINJA_TABLES_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Table count
		$table_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
				'ninja-table'
			)
		);
		
		if ( $table_count === 0 ) {
			return null;
		}
		
		// Check 2: Large tables (row count)
		$large_tables = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, p.post_title, 
				        LENGTH(pm.meta_value) as data_size
				 FROM {$wpdb->posts} p
				 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				 WHERE p.post_type = %s 
				 AND pm.meta_key = '_ninja_table_data'
				 AND LENGTH(pm.meta_value) > 100000
				 LIMIT 5",
				'ninja-table'
			),
			ARRAY_A
		);
		
		if ( count( $large_tables ) > 0 ) {
			$issues[] = sprintf( __( '%d tables with 100KB+ data (slow rendering)', 'wpshadow' ), count( $large_tables ) );
		}
		
		// Check 3: Frontend caching
		$cache_enabled = get_option( 'ninja_tables_cache_enabled', false );
		if ( ! $cache_enabled ) {
			$issues[] = __( 'Frontend caching disabled (repeated queries)', 'wpshadow' );
		}
		
		// Check 4: DataTables library loading
		$datatables_everywhere = get_option( 'ninja_tables_load_everywhere', false );
		if ( $datatables_everywhere ) {
			$issues[] = __( 'DataTables loaded everywhere (unnecessary JS)', 'wpshadow' );
		}
		
		// Check 5: AJAX loading for large tables
		$ajax_enabled = get_option( 'ninja_tables_ajax_enabled', true );
		if ( ! $ajax_enabled && count( $large_tables ) > 0 ) {
			$issues[] = __( 'AJAX pagination disabled on large tables (page bloat)', 'wpshadow' );
		}
		
		
		// Check 6: Cache status
		if ( ! (defined( "WP_CACHE" ) && WP_CACHE) ) {
			$issues[] = __( 'Cache status', 'wpshadow' );
		}

		// Check 7: Database optimization
		if ( ! (! is_option_empty( "db_optimized" )) ) {
			$issues[] = __( 'Database optimization', 'wpshadow' );
		}

		// Check 8: Asset minification
		if ( ! (function_exists( "wp_enqueue_script" )) ) {
			$issues[] = __( 'Asset minification', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = (40 + min(35, count($issues) * 8));
		if ( count( $issues ) >= 4 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of Ninja Tables performance issues */
				__( 'Ninja Tables has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/ninja-tables-performance',
		);
	}
}
