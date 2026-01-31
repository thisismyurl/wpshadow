<?php
/**
 * Enfold Theme Avia Builder Performance Diagnostic
 *
 * Enfold Theme Avia Builder Performance needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1309.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enfold Theme Avia Builder Performance Diagnostic Class
 *
 * @since 1.1309.0000
 */
class Diagnostic_EnfoldThemeAviaBuilderPerformance extends Diagnostic_Base {

	protected static $slug = 'enfold-theme-avia-builder-performance';
	protected static $title = 'Enfold Theme Avia Builder Performance';
	protected static $description = 'Enfold Theme Avia Builder Performance needs optimization';
	protected static $family = 'performance';

	public static function check() {
		// Check for Enfold theme
		$theme = wp_get_theme();
		if ( 'Enfold' !== $theme->name && 'Enfold' !== $theme->parent_theme ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Builder content exists
		$builder_pages = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE '%[av_%' AND post_status = 'publish'"
		);
		
		if ( $builder_pages === 0 ) {
			return null;
		}
		
		// Check 2: Builder cache enabled
		$cache_enabled = get_option( 'avia_builder_cache', false );
		if ( ! $cache_enabled ) {
			$issues[] = __( 'Avia Builder element cache not enabled', 'wpshadow' );
		}
		
		// Check 3: Large builder layouts
		$large_layouts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_content LIKE '%[av_%'
			 AND LENGTH(post_content) > 100000"
		);
		
		if ( $large_layouts > 5 ) {
			$issues[] = sprintf( __( '%d pages with very large builder layouts (>100KB)', 'wpshadow' ), $large_layouts );
		}
		
		// Check 4: Animation overload
		$heavy_animations = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_content LIKE '%av_animation%'
			 AND (LENGTH(post_content) - LENGTH(REPLACE(post_content, 'av_animation', ''))) > 500"
		);
		
		if ( $heavy_animations > 3 ) {
			$issues[] = sprintf( __( '%d pages with excessive animations (render performance)', 'wpshadow' ), $heavy_animations );
		}
		
		// Check 5: Layout Builder compression
		$compress_layouts = get_option( 'avia_compress_builder_output', true );
		if ( ! $compress_layouts ) {
			$issues[] = __( 'Builder output compression disabled (larger HTML)', 'wpshadow' );
		}
		
		// Check 6: Asset merging
		$merge_css = get_option( 'avia_merge_css', false );
		if ( ! $merge_css && $builder_pages > 10 ) {
			$issues[] = __( 'CSS file merging not enabled (multiple HTTP requests)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of performance issues */
				__( 'Enfold Avia Builder has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/enfold-theme-avia-builder-performance',
		);
	}
}
