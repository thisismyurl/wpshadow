<?php
/**
 * Kadence Theme Header Builder Queries Diagnostic
 *
 * Kadence Theme Header Builder Queries needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1301.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kadence Theme Header Builder Queries Diagnostic Class
 *
 * @since 1.1301.0000
 */
class Diagnostic_KadenceThemeHeaderBuilderQueries extends Diagnostic_Base {

	protected static $slug = 'kadence-theme-header-builder-queries';
	protected static $title = 'Kadence Theme Header Builder Queries';
	protected static $description = 'Kadence Theme Header Builder Queries needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Kadence theme
		$theme = wp_get_theme();
		if ( 'Kadence' !== $theme->name && 'Kadence' !== $theme->parent_theme ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Header builder enabled
		$header_builder = get_theme_mod( 'header_builder_enable', false );
		if ( ! $header_builder ) {
			return null;
		}
		
		// Check 2: Conditional header queries
		$conditional_headers = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'_kad_header_conditional'
			)
		);
		
		if ( $conditional_headers > 5 ) {
			$issues[] = sprintf( __( '%d conditional headers (increase query load)', 'wpshadow' ), $conditional_headers );
		}
		
		// Check 3: Header element count
		$header_elements = get_theme_mod( 'header_builder_elements', array() );
		if ( is_array( $header_elements ) && count( $header_elements ) > 15 ) {
			$issues[] = sprintf( __( '%d header elements (simplification recommended)', 'wpshadow' ), count( $header_elements ) );
		}
		
		// Check 4: Mobile header optimization
		$mobile_separate = get_theme_mod( 'header_mobile_separate', false );
		if ( ! $mobile_separate && is_array( $header_elements ) && count( $header_elements ) > 10 ) {
			$issues[] = __( 'Mobile using desktop header elements (performance impact)', 'wpshadow' );
		}
		
		// Check 5: Sticky header performance
		$sticky_enabled = get_theme_mod( 'header_sticky', false );
		$sticky_shrink = get_theme_mod( 'header_sticky_shrink', false );
		
		if ( $sticky_enabled && ! $sticky_shrink ) {
			$issues[] = __( 'Sticky header without shrink (layout shift issues)', 'wpshadow' );
		}
		
		// Check 6: Header caching
		$cache_enabled = get_option( 'kadence_header_cache', false );
		if ( ! $cache_enabled && $conditional_headers > 3 ) {
			$issues[] = __( 'Header element caching not enabled', 'wpshadow' );
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
				/* translators: %s: list of query issues */
				__( 'Kadence header builder has %d query optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/kadence-theme-header-builder-queries',
		);
	}
}
