<?php
/**
 * Bricks Builder Template Caching Diagnostic
 *
 * Bricks Builder Template Caching issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.821.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bricks Builder Template Caching Diagnostic Class
 *
 * @since 1.821.0000
 */
class Diagnostic_BricksBuilderTemplateCaching extends Diagnostic_Base {

	protected static $slug = 'bricks-builder-template-caching';
	protected static $title = 'Bricks Builder Template Caching';
	protected static $description = 'Bricks Builder Template Caching issues found';
	protected static $family = 'performance';

	public static function check() {
		// Check for Bricks Builder
		$has_bricks = defined( 'BRICKS_VERSION' ) ||
		              class_exists( 'Bricks\Theme' ) ||
		              function_exists( 'bricks_is_builder_main' );
		
		if ( ! $has_bricks ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Template caching
		$cache_templates = get_option( 'bricks_cache_templates', 'off' );
		if ( 'off' === $cache_templates ) {
			$issues[] = __( 'Template caching disabled (slow loading)', 'wpshadow' );
		}
		
		// Check 2: CSS generation
		$css_inline = get_option( 'bricks_css_inline', 'off' );
		if ( 'off' === $css_inline && 'on' === $cache_templates ) {
			$issues[] = __( 'External CSS files (extra HTTP requests)', 'wpshadow' );
		}
		
		// Check 3: Query loop caching
		$cache_queries = get_option( 'bricks_cache_query_loops', 'off' );
		if ( 'off' === $cache_queries ) {
			$issues[] = __( 'Query loops not cached (redundant DB queries)', 'wpshadow' );
		}
		
		// Check 4: Dynamic data caching
		$cache_dynamic = get_option( 'bricks_cache_dynamic_data', 'off' );
		if ( 'off' === $cache_dynamic ) {
			$issues[] = __( 'Dynamic data not cached (performance hit)', 'wpshadow' );
		}
		
		// Check 5: Builder mode indicator
		$builder_indicator = get_option( 'bricks_show_builder_indicator', 'on' );
		if ( 'on' === $builder_indicator ) {
			$issues[] = __( 'Builder indicator visible (unprofessional)', 'wpshadow' );
		}
		
		// Check 6: Asset optimization
		$optimize_assets = get_option( 'bricks_optimize_assets', 'off' );
		if ( 'off' === $optimize_assets ) {
			$issues[] = __( 'Assets not optimized (larger file sizes)', 'wpshadow' );
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
				__( 'Bricks Builder has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/bricks-builder-template-caching',
		);
	}
}
