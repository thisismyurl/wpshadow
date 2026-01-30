<?php
/**
 * Elementor Pro Theme Builder Queries Diagnostic
 *
 * Elementor Pro Theme Builder Queries issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.792.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro Theme Builder Queries Diagnostic Class
 *
 * @since 1.792.0000
 */
class Diagnostic_ElementorProThemeBuilderQueries extends Diagnostic_Base {

	protected static $slug = 'elementor-pro-theme-builder-queries';
	protected static $title = 'Elementor Pro Theme Builder Queries';
	protected static $description = 'Elementor Pro Theme Builder Queries issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return null;
		}

		$has_pro = defined( 'ELEMENTOR_PRO_VERSION' ) ||
		           class_exists( 'ElementorPro\Plugin' );

		if ( ! $has_pro ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: Theme templates count
		$template_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'elementor_library'
			)
		);

		if ( $template_count > 50 ) {
			$issues[] = sprintf( __( '%d templates (slow loading)', 'wpshadow' ), $template_count );
		}

		// Check 2: Query caching
		$cache_queries = get_option( 'elementor_cache_query_results', 'no' );
		if ( 'no' === $cache_queries ) {
			$issues[] = __( 'Theme Builder queries not cached (redundant DB hits)', 'wpshadow' );
		}

		// Check 3: Display conditions
		$condition_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_conditions'"
		);

		if ( $condition_count > 100 ) {
			$issues[] = sprintf( __( '%d display conditions (complex evaluation)', 'wpshadow' ), $condition_count );
		}

		// Check 4: Loop grids
		$loop_grids = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE '%loop-grid%'"
		);

		if ( $loop_grids > 20 ) {
			$issues[] = sprintf( __( '%d loop grids (heavy queries)', 'wpshadow' ), $loop_grids );
		}

		// Check 5: Dynamic tags
		$dynamic_tags = get_option( 'elementor_disable_dynamic_tags', 'no' );
		if ( 'no' === $dynamic_tags ) {
			$issues[] = __( 'All dynamic tags enabled (performance overhead)', 'wpshadow' );
		}

		// Check 6: CSS regeneration
		$css_print_method = get_option( 'elementor_css_print_method', 'external' );
		if ( 'internal' === $css_print_method ) {
			$issues[] = __( 'Internal CSS method (larger page size)', 'wpshadow' );
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
				__( 'Elementor Pro Theme Builder has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/elementor-pro-theme-builder-queries',
		);
	}
}
