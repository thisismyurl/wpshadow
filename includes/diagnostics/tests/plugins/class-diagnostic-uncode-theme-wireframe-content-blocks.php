<?php
/**
 * Uncode Theme Wireframe Content Blocks Diagnostic
 *
 * Uncode Theme Wireframe Content Blocks needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1331.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uncode Theme Wireframe Content Blocks Diagnostic Class
 *
 * @since 1.1331.0000
 */
class Diagnostic_UncodeThemeWireframeContentBlocks extends Diagnostic_Base {

	protected static $slug = 'uncode-theme-wireframe-content-blocks';
	protected static $title = 'Uncode Theme Wireframe Content Blocks';
	protected static $description = 'Uncode Theme Wireframe Content Blocks needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Uncode theme
		$theme = wp_get_theme();
		if ( 'Uncode' !== $theme->name && 'Uncode' !== $theme->parent_theme ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Wireframe library enabled
		$wireframes_enabled = get_option( 'uncode_wireframes_enabled', false );
		if ( ! $wireframes_enabled ) {
			return null;
		}
		
		// Check 2: Wireframe library size
		$wireframe_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'_uncode_wireframe'
			)
		);
		
		if ( $wireframe_count > 100 ) {
			$issues[] = sprintf( __( '%d wireframe blocks in library (consider pruning)', 'wpshadow' ), $wireframe_count );
		}
		
		// Check 3: Lazy loading for blocks
		$lazy_load = get_option( 'uncode_wireframe_lazy_load', false );
		if ( ! $lazy_load && $wireframe_count > 50 ) {
			$issues[] = __( 'Wireframe lazy loading not enabled (slow page builder)', 'wpshadow' );
		}
		
		// Check 4: Block preview caching
		$cache_previews = get_option( 'uncode_cache_wireframe_previews', false );
		if ( ! $cache_previews ) {
			$issues[] = __( 'Wireframe preview caching disabled (editor performance)', 'wpshadow' );
		}
		
		// Check 5: Custom wireframes
		$custom_wireframes = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
				'_uncode_wireframe_type',
				'custom'
			)
		);
		
		if ( $custom_wireframes > 20 ) {
			$issues[] = sprintf( __( '%d custom wireframes (consolidation recommended)', 'wpshadow' ), $custom_wireframes );
		}
		
		// Check 6: Conditional loading
		$conditional = get_option( 'uncode_wireframe_conditional_load', false );
		if ( ! $conditional ) {
			$issues[] = __( 'Wireframe blocks load on all pages (unnecessary overhead)', 'wpshadow' );
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
				/* translators: %s: list of optimization issues */
				__( 'Uncode wireframe blocks have %d optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/uncode-theme-wireframe-content-blocks',
		);
	}
}
