<?php
/**
 * X Theme Content Builder Performance Diagnostic
 *
 * X Theme Content Builder Performance needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1329.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * X Theme Content Builder Performance Diagnostic Class
 *
 * @since 1.1329.0000
 */
class Diagnostic_XThemeContentBuilderPerformance extends Diagnostic_Base {

	protected static $slug = 'x-theme-content-builder-performance';
	protected static $title = 'X Theme Content Builder Performance';
	protected static $description = 'X Theme Content Builder Performance needs optimization';
	protected static $family = 'performance';

	public static function check() {
		// Check for X Theme or Cornerstone
		$theme = wp_get_theme();
		if ( 'X' !== $theme->name && 'X' !== $theme->parent_theme && ! class_exists( 'Cornerstone_Plugin' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Cornerstone builder cache
		$builder_cache = get_option( 'cornerstone_cache_enabled', false );
		if ( ! $builder_cache ) {
			$issues[] = __( 'Cornerstone builder cache not enabled', 'wpshadow' );
		}
		
		// Check 2: Element count per page
		$heavy_pages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id, LENGTH(meta_value) as size FROM {$wpdb->postmeta}
				 WHERE meta_key = %s AND LENGTH(meta_value) > %d",
				'_cornerstone_data',
				50000
			)
		);
		
		if ( ! empty( $heavy_pages ) ) {
			$issues[] = sprintf( __( '%d pages with heavy element data (>50KB)', 'wpshadow' ), count( $heavy_pages ) );
		}
		
		// Check 3: Inline CSS optimization
		$optimize_css = get_option( 'x_css_cache_mode', 'inline' );
		if ( $optimize_css === 'inline' ) {
			$issues[] = __( 'CSS delivered inline (consider file mode for caching)', 'wpshadow' );
		}
		
		// Check 4: Lazy rendering
		$lazy_render = get_option( 'cornerstone_lazy_render', false );
		if ( ! $lazy_render ) {
			$issues[] = __( 'Lazy rendering not enabled (slower page loads)', 'wpshadow' );
		}
		
		// Check 5: Builder revision cleanup
		$revisions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'_cornerstone_data_revision'
			)
		);
		
		if ( $revisions > 100 ) {
			$issues[] = sprintf( __( '%d builder revisions stored (cleanup recommended)', 'wpshadow' ), $revisions );
		}
		
		// Check 6: Global blocks caching
		$global_blocks = get_option( 'cornerstone_cache_global_blocks', false );
		if ( ! $global_blocks ) {
			$issues[] = __( 'Global blocks not cached (repeated queries)', 'wpshadow' );
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
				/* translators: %s: list of performance issues */
				__( 'X Theme content builder has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/x-theme-content-builder-performance',
		);
	}
}
