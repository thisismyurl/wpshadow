<?php
/**
 * Wordpress Term Meta Performance Diagnostic
 *
 * Wordpress Term Meta Performance issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1282.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Term Meta Performance Diagnostic Class
 *
 * @since 1.1282.0000
 */
class Diagnostic_WordpressTermMetaPerformance extends Diagnostic_Base {

	protected static $slug = 'wordpress-term-meta-performance';
	protected static $title = 'Wordpress Term Meta Performance';
	protected static $description = 'Wordpress Term Meta Performance issue detected';
	protected static $family = 'performance';

	public static function check() {
		// WordPress core feature - always available
		global $wpdb;
		$issues = array();
		
		// Check 1: Total term meta count
		$meta_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->termmeta}" );
		
		if ( $meta_count === 0 ) {
			return null;
		}
		
		if ( $meta_count > 5000 ) {
			$issues[] = sprintf( __( '%d term meta entries (consider optimization)', 'wpshadow' ), $meta_count );
		}
		
		// Check 2: Terms with excessive meta
		$heavy_terms = $wpdb->get_var(
			"SELECT COUNT(DISTINCT term_id) FROM {$wpdb->termmeta}
			 GROUP BY term_id HAVING COUNT(*) > 20"
		);
		
		if ( $heavy_terms > 10 ) {
			$issues[] = sprintf( __( '%d terms with 20+ meta entries (slow queries)', 'wpshadow' ), $heavy_terms );
		}
		
		// Check 3: Orphaned term meta
		$orphan_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->termmeta} tm
			 LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id
			 WHERE t.term_id IS NULL"
		);
		
		if ( $orphan_meta > 50 ) {
			$issues[] = sprintf( __( '%d orphaned term meta entries (cleanup needed)', 'wpshadow' ), $orphan_meta );
		}
		
		// Check 4: Object caching
		$has_object_cache = wp_using_ext_object_cache();
		if ( ! $has_object_cache && $meta_count > 1000 ) {
			$issues[] = __( 'No object caching with large term meta (slow taxonomy queries)', 'wpshadow' );
		}
		
		// Check 5: Large taxonomy counts
		$taxonomies = get_taxonomies( array( 'public' => true ), 'names' );
		foreach ( $taxonomies as $taxonomy ) {
			$term_count = wp_count_terms( array( 'taxonomy' => $taxonomy ) );
			if ( $term_count > 1000 ) {
				$issues[] = sprintf(
					/* translators: 1: taxonomy name, 2: term count */
					__( 'Taxonomy %1$s has %2$d terms (performance concern)', 'wpshadow' ),
					$taxonomy,
					$term_count
				);
				break; // Only report one
			}
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
				/* translators: %s: list of term meta issues */
				__( 'WordPress term meta has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/wordpress-term-meta-performance',
		);
	}
}
