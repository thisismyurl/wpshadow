<?php
/**
 * Wpbakery Page Builder Grid Builder Diagnostic
 *
 * Wpbakery Page Builder Grid Builder issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.829.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpbakery Page Builder Grid Builder Diagnostic Class
 *
 * @since 1.829.0000
 */
class Diagnostic_WpbakeryPageBuilderGridBuilder extends Diagnostic_Base {

	protected static $slug = 'wpbakery-page-builder-grid-builder';
	protected static $title = 'Wpbakery Page Builder Grid Builder';
	protected static $description = 'Wpbakery Page Builder Grid Builder issues found';
	protected static $family = 'functionality';

	public static function check() {
		// Check for WPBakery Page Builder
		if ( ! defined( 'WPB_VC_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Grid builder posts count
		global $wpdb;
		$grid_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_wpb_post_custom_css'"
		);
		if ( $grid_posts > 100 ) {
			$issues[] = sprintf( __( '%d grid builder posts (performance)', 'wpshadow' ), $grid_posts );
		}
		
		// Check 2: Custom CSS inline
		$inline_css = get_option( 'wpb_js_inline_css', 'yes' );
		if ( 'yes' === $inline_css ) {
			$issues[] = __( 'Inline CSS enabled (page bloat)', 'wpshadow' );
		}
		
		// Check 3: Grid AJAX loading
		$ajax_loading = get_option( 'wpb_grid_ajax', 'no' );
		if ( 'no' === $ajax_loading ) {
			$issues[] = __( 'No AJAX loading (slow grid rendering)', 'wpshadow' );
		}
		
		// Check 4: Grid query caching
		$query_cache = get_option( 'wpb_grid_query_cache', 'no' );
		if ( 'no' === $query_cache ) {
			$issues[] = __( 'Grid queries not cached (repeated DB hits)', 'wpshadow' );
		}
		
		// Check 5: Template count
		$templates = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'vc_grid_item'
			)
		);
		if ( $templates > 50 ) {
			$issues[] = sprintf( __( '%d grid templates (UI clutter)', 'wpshadow' ), $templates );
		}
		
		// Check 6: Lazy loading
		$lazy_load = get_option( 'wpb_grid_lazy_load', 'no' );
		if ( 'no' === $lazy_load ) {
			$issues[] = __( 'Lazy loading disabled (slow page load)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'WPBakery Page Builder has %d grid builder issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wpbakery-page-builder-grid-builder',
		);
	}
}
