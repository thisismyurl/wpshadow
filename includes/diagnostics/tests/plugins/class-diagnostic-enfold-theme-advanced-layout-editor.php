<?php
/**
 * Enfold Theme Advanced Layout Editor Diagnostic
 *
 * Enfold Theme Advanced Layout Editor needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1310.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enfold Theme Advanced Layout Editor Diagnostic Class
 *
 * @since 1.1310.0000
 */
class Diagnostic_EnfoldThemeAdvancedLayoutEditor extends Diagnostic_Base {

	protected static $slug = 'enfold-theme-advanced-layout-editor';
	protected static $title = 'Enfold Theme Advanced Layout Editor';
	protected static $description = 'Enfold Theme Advanced Layout Editor needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		$theme = wp_get_theme();
		if ( 'Enfold' !== $theme->get( 'Name' ) && 'Enfold' !== $theme->get_template() ) {
			return null;
		}

		$issues = array();

		// Check if Advanced Layout Editor is enabled
		$alb_enabled = get_option( 'avia_builder_active', '1' );
		if ( '0' === $alb_enabled ) {
			$issues[] = 'Advanced Layout Builder disabled';
		}

		// Check for pages using ALB
		global $wpdb;
		$alb_pages = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
				'_aviaLayoutBuilder_active',
				'active'
			)
		);

		if ( $alb_pages > 0 ) {
			// Check for element caching
			$cache_enabled = get_option( 'avia_builder_cache', '0' );
			if ( '0' === $cache_enabled && $alb_pages > 10 ) {
				$issues[] = 'element caching disabled with many ALB pages';
			}

			// Check for CSS file generation
			$css_file = get_option( 'avia_builder_css_file', '0' );
			if ( '0' === $css_file ) {
				$issues[] = 'ALB CSS inline (not generated as files)';
			}

			// Check for excessive shortcodes
			$complex_pages = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->posts}
				 WHERE post_content LIKE '%[av_%'
				 AND (LENGTH(post_content) - LENGTH(REPLACE(post_content, '[av_', ''))) > 100"
			);

			if ( $complex_pages > 0 ) {
				$issues[] = "pages with excessive ALB elements ({$complex_pages} very complex pages)";
			}
		}

		// Check for font loading optimization
		$font_optimization = get_option( 'avia_font_optimization', '0' );
		if ( '0' === $font_optimization ) {
			$issues[] = 'font loading not optimized for ALB elements';
		}

		// Check for element preloading
		$preload = get_option( 'avia_preload_alb_elements', '0' );
		if ( '1' === $preload ) {
			$issues[] = 'preloading all ALB elements (loads unused element assets)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Enfold Advanced Layout Editor optimization issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/enfold-theme-advanced-layout-editor',
			);
		}

		return null;
	}
}
