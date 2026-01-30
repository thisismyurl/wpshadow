<?php
/**
 * Wordpress Taxonomy Query Optimization Diagnostic
 *
 * Wordpress Taxonomy Query Optimization issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1283.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Taxonomy Query Optimization Diagnostic Class
 *
 * @since 1.1283.0000
 */
class Diagnostic_WordpressTaxonomyQueryOptimization extends Diagnostic_Base {

	protected static $slug = 'wordpress-taxonomy-query-optimization';
	protected static $title = 'Wordpress Taxonomy Query Optimization';
	protected static $description = 'Wordpress Taxonomy Query Optimization issue detected';
	protected static $family = 'performance';

	public static function check() {
		// WordPress core always available
		global $wpdb;
		$issues = array();
		
		// Check 1: Large taxonomy term counts
		$large_taxonomies = $wpdb->get_results(
			"SELECT taxonomy, COUNT(*) as term_count 
			 FROM {$wpdb->term_taxonomy} 
			 GROUP BY taxonomy 
			 HAVING term_count > 1000"
		);
		
		if ( ! empty( $large_taxonomies ) ) {
			foreach ( $large_taxonomies as $tax ) {
				$issues[] = sprintf( __( 'Taxonomy "%s" has %d terms (may slow queries)', 'wpshadow' ), $tax->taxonomy, $tax->term_count );
			}
		}
		
		// Check 2: Term meta query usage
		$term_meta_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->termmeta}" );
		if ( $term_meta_count > 5000 ) {
			$issues[] = sprintf( __( '%d term meta entries (meta queries may be slow)', 'wpshadow' ), $term_meta_count );
		}
		
		// Check 3: Hierarchical taxonomies with deep nesting
		$hierarchical_depth = $wpdb->get_results(
			"SELECT t.taxonomy, MAX(t.count) as max_depth 
			 FROM {$wpdb->term_taxonomy} t 
			 WHERE t.parent > 0 
			 GROUP BY t.taxonomy"
		);
		
		if ( ! empty( $hierarchical_depth ) ) {
			foreach ( $hierarchical_depth as $depth ) {
				if ( $depth->max_depth > 100 ) {
					$issues[] = sprintf( __( 'Taxonomy "%s" has deep hierarchy (performance impact)', 'wpshadow' ), $depth->taxonomy );
				}
			}
		}
		
		// Check 4: Large term_relationships table
		$relationships_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->term_relationships}" );
		if ( $relationships_count > 50000 ) {
			$issues[] = sprintf( __( '%d term relationships (index optimization needed)', 'wpshadow' ), $relationships_count );
		}
		
		// Check 5: Object caching for taxonomy queries
		if ( ! wp_using_ext_object_cache() && $relationships_count > 10000 ) {
			$issues[] = __( 'No external object cache for large taxonomy data', 'wpshadow' );
		}
		
		// Check 6: Unused taxonomies registered
		$registered_taxonomies = get_taxonomies( array(), 'names' );
		$empty_taxonomies = 0;
		
		foreach ( $registered_taxonomies as $taxonomy ) {
			$term_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s",
					$taxonomy
				)
			);
			
			if ( $term_count === '0' && ! in_array( $taxonomy, array( 'category', 'post_tag', 'nav_menu' ), true ) ) {
				$empty_taxonomies++;
			}
		}
		
		if ( $empty_taxonomies > 5 ) {
			$issues[] = sprintf( __( '%d registered but unused taxonomies', 'wpshadow' ), $empty_taxonomies );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 70;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of optimization issues */
				__( 'WordPress taxonomy queries have %d optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wordpress-taxonomy-query-optimization',
		);
	}
}
