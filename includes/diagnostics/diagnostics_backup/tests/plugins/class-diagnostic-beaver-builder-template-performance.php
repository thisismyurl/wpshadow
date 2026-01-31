<?php
/**
 * Beaver Builder Template Performance Diagnostic
 *
 * Beaver Builder templates loading slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.345.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Template Performance Diagnostic Class
 *
 * @since 1.345.0000
 */
class Diagnostic_BeaverBuilderTemplatePerformance extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-template-performance';
	protected static $title = 'Beaver Builder Template Performance';
	protected static $description = 'Beaver Builder templates loading slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Template caching
		$cache = get_option( 'bb_template_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Template caching not enabled';
		}

		// Check 2: Asset optimization
		$assets = get_option( 'bb_asset_optimization_enabled', 0 );
		if ( ! $assets ) {
			$issues[] = 'Asset optimization not enabled';
		}

		// Check 3: Lazy loading
		$lazy = get_option( 'bb_lazy_loading_enabled', 0 );
		if ( ! $lazy ) {
			$issues[] = 'Lazy loading not enabled';
		}

		// Check 4: Image optimization
		$images = get_option( 'bb_template_image_optimization_enabled', 0 );
		if ( ! $images ) {
			$issues[] = 'Template image optimization not enabled';
		}

		// Check 5: CSS/JS minification
		$minify = get_option( 'bb_template_minification_enabled', 0 );
		if ( ! $minify ) {
			$issues[] = 'CSS/JS minification not enabled';
		}

		// Check 6: Performance monitoring
		$monitor = get_option( 'bb_template_performance_monitoring', 0 );
		if ( ! $monitor ) {
			$issues[] = 'Template performance monitoring not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 35;
			$threat_multiplier = 6;
			$max_threat = 65;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d template performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-template-performance',
			);
		}

		return null;
	}
}
