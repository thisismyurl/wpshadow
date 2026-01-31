<?php
/**
 * Gutenberg Block Patterns Database Diagnostic
 *
 * Gutenberg Block Patterns Database issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1241.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gutenberg Block Patterns Database Diagnostic Class
 *
 * @since 1.1241.0000
 */
class Diagnostic_GutenbergBlockPatternsDatabase extends Diagnostic_Base {

	protected static $slug = 'gutenberg-block-patterns-database';
	protected static $title = 'Gutenberg Block Patterns Database';
	protected static $description = 'Gutenberg Block Patterns Database issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Block patterns are a core WordPress feature (5.5+)
		if ( ! function_exists( 'register_block_pattern' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Count registered patterns
		$pattern_registry = WP_Block_Patterns_Registry::get_instance();
		$patterns = $pattern_registry->get_all_registered();
		
		if ( count( $patterns ) > 100 ) {
			$issues[] = sprintf( __( '%d registered patterns (memory overhead)', 'wpshadow' ), count( $patterns ) );
		}
		
		// Check 2: Custom patterns in database
		global $wpdb;
		$custom_patterns = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'wp_block'
			)
		);
		
		if ( $custom_patterns > 50 ) {
			$issues[] = sprintf( __( '%d reusable blocks (database bloat)', 'wpshadow' ), $custom_patterns );
		}
		
		// Check 3: Pattern categories
		$category_registry = WP_Block_Pattern_Categories_Registry::get_instance();
		$categories = $category_registry->get_all_registered();
		
		if ( count( $categories ) > 20 ) {
			$issues[] = sprintf( __( '%d pattern categories (UI clutter)', 'wpshadow' ), count( $categories ) );
		}
		
		// Check 4: Remote patterns enabled
		$remote_patterns = get_option( 'should_load_remote_block_patterns', true );
		if ( $remote_patterns ) {
			$issues[] = __( 'Loading remote patterns (external API calls)', 'wpshadow' );
		}
		
		// Check 5: Pattern caching
		$pattern_cache = wp_cache_get( 'core_block_patterns', 'patterns' );
		if ( false === $pattern_cache ) {
			$issues[] = __( 'Pattern cache not set (regenerated each request)', 'wpshadow' );
		}
		
		
		// Check 6: Feature initialization
		if ( ! (get_option( "features_init" ) !== false) ) {
			$issues[] = __( 'Feature initialization', 'wpshadow' );
		}

		// Check 7: Database tables
		if ( ! (! empty( $GLOBALS["wpdb"] )) ) {
			$issues[] = __( 'Database tables', 'wpshadow' );
		}

		// Check 8: Hook registration
		if ( ! (has_action( "init" )) ) {
			$issues[] = __( 'Hook registration', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = (40 + min(35, count($issues) * 8));
		if ( count( $issues ) >= 4 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of block pattern issues */
				__( 'Gutenberg block patterns have %d database issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/gutenberg-block-patterns-database',
		);
	}
}
