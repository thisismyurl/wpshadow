<?php
/**
 * Pods Framework Template Performance Diagnostic
 *
 * Pods Framework Template Performance issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1055.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pods Framework Template Performance Diagnostic Class
 *
 * @since 1.1055.0000
 */
class Diagnostic_PodsFrameworkTemplatePerformance extends Diagnostic_Base {

	protected static $slug = 'pods-framework-template-performance';
	protected static $title = 'Pods Framework Template Performance';
	protected static $description = 'Pods Framework Template Performance issue detected';
	protected static $family = 'performance';

	public static function check() {

		$issues = array();

		// Check 1: Verify template caching is enabled
		$template_cache = get_option( 'pods_template_cache_enabled', false );
		if ( ! $template_cache ) {
			$issues[] = __( 'Pods template caching not enabled', 'wpshadow' );
		}

		// Check 2: Check query optimization
		$query_optimization = get_option( 'pods_query_optimization', false );
		if ( ! $query_optimization ) {
			$issues[] = __( 'Pods query optimization not enabled', 'wpshadow' );
		}

		// Check 3: Verify field loading strategy
		$field_loading = get_option( 'pods_field_loading_strategy', '' );
		if ( 'lazy' !== $field_loading ) {
			$issues[] = __( 'Field loading strategy not optimized', 'wpshadow' );
		}

		// Check 4: Check template complexity limits
		$complexity_limit = get_option( 'pods_template_complexity_limit', false );
		if ( ! $complexity_limit ) {
			$issues[] = __( 'Template complexity limits not configured', 'wpshadow' );
		}

		// Check 5: Verify render caching
		$render_cache = get_transient( 'pods_template_render_cache' );
		if ( false === $render_cache ) {
			$issues[] = __( 'Template render caching not active', 'wpshadow' );
		}

		// Check 6: Check AJAX pagination configuration
		$ajax_pagination = get_option( 'pods_ajax_pagination', false );
		if ( ! $ajax_pagination ) {
			$issues[] = __( 'AJAX pagination not enabled for templates', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 55 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Pods Framework template performance issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/pods-framework-template-performance',
			);
		}

		return null;
	}
}
