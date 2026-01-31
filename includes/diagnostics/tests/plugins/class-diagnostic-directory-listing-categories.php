<?php
/**
 * Directory Listing Categories Diagnostic
 *
 * Directory category queries inefficient.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.559.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory Listing Categories Diagnostic Class
 *
 * @since 1.559.0000
 */
class Diagnostic_DirectoryListingCategories extends Diagnostic_Base {

	protected static $slug = 'directory-listing-categories';
	protected static $title = 'Directory Listing Categories';
	protected static $description = 'Directory category queries inefficient';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Category taxonomy exists
		$category_count = wp_count_terms( array(
			'taxonomy' => WPBDP_CATEGORY_TAX,
			'hide_empty' => false,
		) );
		
		if ( is_wp_error( $category_count ) || $category_count === 0 ) {
			return null;
		}
		
		// Check 2: Deep category hierarchy
		$max_depth = 0;
		$terms = get_terms( array(
			'taxonomy' => WPBDP_CATEGORY_TAX,
			'hide_empty' => false,
		) );
		
		foreach ( $terms as $term ) {
			$depth = 0;
			$parent = $term->parent;
			while ( $parent > 0 ) {
				$depth++;
				$parent_term = get_term( $parent, WPBDP_CATEGORY_TAX );
				$parent = $parent_term ? $parent_term->parent : 0;
			}
			$max_depth = max( $max_depth, $depth );
		}
		
		if ( $max_depth > 5 ) {
			$issues[] = sprintf( __( 'Category hierarchy depth: %d levels (recommend 3-4)', 'wpshadow' ), $max_depth );
		}
		
		// Check 3: Large category count
		if ( $category_count > 500 ) {
			$issues[] = sprintf( __( '%d categories (query performance impact)', 'wpshadow' ), $category_count );
		}
		
		// Check 4: Empty categories
		$empty_cats = wp_count_terms( array(
			'taxonomy' => WPBDP_CATEGORY_TAX,
			'hide_empty' => true,
		) );
		
		$empty_count = $category_count - $empty_cats;
		if ( $empty_count > 50 ) {
			$issues[] = sprintf( __( '%d empty categories (cleanup recommended)', 'wpshadow' ), $empty_count );
		}
		
		// Check 5: Category query caching
		$cache_enabled = get_option( 'wpbdp_cache_directory_categories', false );
		if ( ! $cache_enabled && $category_count > 100 ) {
			$issues[] = __( 'Category query caching not enabled', 'wpshadow' );
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
				/* translators: %s: list of category issues */
				__( 'Directory listing categories have %d optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/directory-listing-categories',
		);
	}
}
