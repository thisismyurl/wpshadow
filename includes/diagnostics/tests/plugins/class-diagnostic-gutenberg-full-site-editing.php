<?php
/**
 * Gutenberg Full Site Editing Diagnostic
 *
 * Gutenberg Full Site Editing issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1240.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gutenberg Full Site Editing Diagnostic Class
 *
 * @since 1.1240.0000
 */
class Diagnostic_GutenbergFullSiteEditing extends Diagnostic_Base {

	protected static $slug = 'gutenberg-full-site-editing';
	protected static $title = 'Gutenberg Full Site Editing';
	protected static $description = 'Gutenberg Full Site Editing issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Full Site Editing available in WordPress 5.9+
		global $wp_version;
		if ( version_compare( $wp_version, '5.9', '<' ) ) {
			return null;
		}
		
		// Check if current theme supports block templates
		if ( ! wp_is_block_theme() ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: theme.json validation
		$theme_json_path = get_stylesheet_directory() . '/theme.json';
		if ( ! file_exists( $theme_json_path ) ) {
			$issues[] = __( 'Block theme missing theme.json file', 'wpshadow' );
		} else {
			$theme_json = json_decode( file_get_contents( $theme_json_path ), true );
			if ( null === $theme_json ) {
				$issues[] = __( 'theme.json contains invalid JSON', 'wpshadow' );
			}
		}
		
		// Check 2: Template parts count
		$template_parts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'wp_template_part'
			)
		);
		
		if ( $template_parts > 20 ) {
			$issues[] = sprintf( __( '%d template parts (consolidation recommended)', 'wpshadow' ), $template_parts );
		}
		
		// Check 3: Global styles optimization
		$global_styles = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_content FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s LIMIT 1",
				'wp_global_styles',
				'publish'
			)
		);
		
		if ( ! empty( $global_styles ) && strlen( $global_styles ) > 50000 ) {
			$issues[] = sprintf( __( 'Global styles: %.2f KB (optimization needed)', 'wpshadow' ), strlen( $global_styles ) / 1024 );
		}
		
		// Check 4: Query loop usage
		$query_loops = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE %s AND post_type IN ('wp_template', 'wp_template_part')",
				'%wp:query%'
			)
		);
		
		if ( $query_loops > 5 ) {
			$issues[] = sprintf( __( '%d query loops in templates (caching recommended)', 'wpshadow' ), $query_loops );
		}
		
		// Check 5: Block pattern performance
		$block_patterns = WP_Block_Patterns_Registry::get_instance()->get_all_registered();
		if ( count( $block_patterns ) > 50 ) {
			$issues[] = sprintf( __( '%d registered block patterns (editor performance)', 'wpshadow' ), count( $block_patterns ) );
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
				/* translators: %s: list of FSE issues */
				__( 'Gutenberg Full Site Editing has %d optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/gutenberg-full-site-editing',
		);
	}
}
