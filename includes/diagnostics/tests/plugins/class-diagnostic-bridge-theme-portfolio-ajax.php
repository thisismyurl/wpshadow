<?php
/**
 * Bridge Theme Portfolio Ajax Diagnostic
 *
 * Bridge Theme Portfolio Ajax needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1317.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bridge Theme Portfolio Ajax Diagnostic Class
 *
 * @since 1.1317.0000
 */
class Diagnostic_BridgeThemePortfolioAjax extends Diagnostic_Base {

	protected static $slug = 'bridge-theme-portfolio-ajax';
	protected static $title = 'Bridge Theme Portfolio Ajax';
	protected static $description = 'Bridge Theme Portfolio Ajax needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Bridge theme
		$theme = wp_get_theme();
		if ( 'Bridge' !== $theme->name && 'Bridge' !== $theme->parent_theme ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Portfolio AJAX enabled
		$ajax_portfolio = get_option( 'qode_enable_ajax_portfolio', false );
		if ( ! $ajax_portfolio ) {
			return null;
		}
		
		// Check 2: AJAX pagination caching
		$cache_ajax = get_option( 'qode_cache_ajax_portfolio', false );
		if ( ! $cache_ajax ) {
			$issues[] = __( 'AJAX portfolio pagination not cached', 'wpshadow' );
		}
		
		// Check 3: Image lazy loading
		$lazy_load = get_option( 'qode_portfolio_lazy_load', false );
		if ( ! $lazy_load ) {
			$issues[] = __( 'Portfolio image lazy loading not enabled', 'wpshadow' );
		}
		
		// Check 4: Items per page
		$items_per_page = get_option( 'qode_portfolio_items_per_page', 12 );
		if ( $items_per_page > 24 ) {
			$issues[] = sprintf( __( 'Portfolio showing %d items per page (recommend 12-24)', 'wpshadow' ), $items_per_page );
		}
		
		// Check 5: Infinite scroll vs pagination
		$infinite_scroll = get_option( 'qode_portfolio_infinite_scroll', false );
		$portfolio_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s",
				'portfolio-item',
				'publish'
			)
		);
		
		if ( $infinite_scroll && $portfolio_count > 100 ) {
			$issues[] = __( 'Infinite scroll with large portfolio (memory concerns)', 'wpshadow' );
		}
		
		// Check 6: Filter performance
		$ajax_filter = get_option( 'qode_portfolio_ajax_filter', false );
		if ( $ajax_filter && ! $cache_ajax ) {
			$issues[] = __( 'AJAX filtering without caching (repeated database queries)', 'wpshadow' );
		}
		
		// Check 7: Preload adjacent pages
		$preload = get_option( 'qode_portfolio_preload_pages', false );
		if ( ! $preload && $ajax_portfolio ) {
			$issues[] = __( 'AJAX pagination without preloading (slower navigation)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 65;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 58;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of optimization issues */
				__( 'Bridge theme portfolio AJAX has %d optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/bridge-theme-portfolio-ajax',
		);
	}
}
