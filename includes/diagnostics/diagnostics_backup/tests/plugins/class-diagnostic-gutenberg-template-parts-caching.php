<?php
/**
 * Gutenberg Template Parts Caching Diagnostic
 *
 * Gutenberg Template Parts Caching issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1243.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gutenberg Template Parts Caching Diagnostic Class
 *
 * @since 1.1243.0000
 */
class Diagnostic_GutenbergTemplatePartsCaching extends Diagnostic_Base {

	protected static $slug = 'gutenberg-template-parts-caching';
	protected static $title = 'Gutenberg Template Parts Caching';
	protected static $description = 'Gutenberg Template Parts Caching issue detected';
	protected static $family = 'performance';

	public static function check() {
		// Check if using block themes (FSE)
		if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Template parts count
		$template_parts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'wp_template_part'
			)
		);
		
		if ( $template_parts === 0 ) {
			return null; // No template parts
		}
		
		// Check 2: Template cache
		$cache_enabled = wp_using_ext_object_cache();
		if ( ! $cache_enabled ) {
			$issues[] = __( 'No object cache (templates queried repeatedly)', 'wpshadow' );
		}
		
		// Check 3: Transient cleanup
		$transient_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} 
			WHERE option_name LIKE '_transient_wp_template_%'"
		);
		
		if ( $transient_count > 50 ) {
			$issues[] = sprintf( __( '%d template transients (database bloat)', 'wpshadow' ), $transient_count );
		}
		
		// Check 4: Pattern registry
		$pattern_count = count( WP_Block_Patterns_Registry::get_instance()->get_all_registered() );
		if ( $pattern_count > 100 ) {
			$issues[] = sprintf( __( '%d block patterns (slow editor)', 'wpshadow' ), $pattern_count );
		}
		
		// Check 5: Theme.json caching
		$theme_json_cache = get_transient( 'wp_theme_json_data' );
		if ( false === $theme_json_cache ) {
			$issues[] = __( 'theme.json not cached (repeated parsing)', 'wpshadow' );
		}
		
		// Check 6: Global styles revisions
		$revisions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type = %s AND post_parent IN 
				(SELECT ID FROM {$wpdb->posts} WHERE post_type = %s)",
				'revision',
				'wp_global_styles'
			)
		);
		
		if ( $revisions > 50 ) {
			$issues[] = sprintf( __( '%d global styles revisions (database bloat)', 'wpshadow' ), $revisions );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 67;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 61;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of Gutenberg template caching issues */
				__( 'Gutenberg template parts have %d caching issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/gutenberg-template-parts-caching',
		);
	}
}
