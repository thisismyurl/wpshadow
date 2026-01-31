<?php
/**
 * Wordpress Menu Query Caching Diagnostic
 *
 * Wordpress Menu Query Caching issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1284.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Menu Query Caching Diagnostic Class
 *
 * @since 1.1284.0000
 */
class Diagnostic_WordpressMenuQueryCaching extends Diagnostic_Base {

	protected static $slug = 'wordpress-menu-query-caching';
	protected static $title = 'Wordpress Menu Query Caching';
	protected static $description = 'Wordpress Menu Query Caching issue detected';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'wp_get_nav_menus' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify menu caching is enabled
		$menu_cache_enabled = get_option( 'wp_nav_menu_cache_enabled', false );
		if ( ! $menu_cache_enabled ) {
			$issues[] = __( 'Menu query caching not enabled', 'wpshadow' );
		}

		// Check 2: Check transient usage for menu caching
		$menus = wp_get_nav_menus();
		$cached_menus = 0;
		foreach ( $menus as $menu ) {
			if ( false !== get_transient( 'nav_menu_' . $menu->term_id ) ) {
				$cached_menus++;
			}
		}
		if ( $cached_menus === 0 && count( $menus ) > 0 ) {
			$issues[] = __( 'No menu transient caching detected', 'wpshadow' );
		}

		// Check 3: Verify menu query optimization
		$query_optimization = get_option( 'wp_nav_menu_query_optimization', false );
		if ( ! $query_optimization ) {
			$issues[] = __( 'Menu query optimization not enabled', 'wpshadow' );
		}

		// Check 4: Check cache invalidation strategy
		$cache_invalidation = get_option( 'wp_nav_menu_cache_invalidation', '' );
		if ( empty( $cache_invalidation ) ) {
			$issues[] = __( 'Menu cache invalidation strategy not configured', 'wpshadow' );
		}

		// Check 5: Verify menu count limits for performance
		if ( count( $menus ) > 10 ) {
			$issues[] = __( 'Excessive menu count may impact performance', 'wpshadow' );
		}

		// Check 6: Check menu walker class caching
		$walker_cache = get_option( 'wp_nav_menu_walker_cache', false );
		if ( ! $walker_cache ) {
			$issues[] = __( 'Menu walker class caching not enabled', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 55 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Menu query caching issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-menu-query-caching',
			);
		}

		return null;
	}
}
