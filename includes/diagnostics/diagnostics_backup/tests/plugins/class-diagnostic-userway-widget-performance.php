<?php
/**
 * Userway Widget Performance Diagnostic
 *
 * Userway Widget Performance not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1100.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Userway Widget Performance Diagnostic Class
 *
 * @since 1.1100.0000
 */
class Diagnostic_UserwayWidgetPerformance extends Diagnostic_Base {

	protected static $slug = 'userway-widget-performance';
	protected static $title = 'Userway Widget Performance';
	protected static $description = 'Userway Widget Performance not compliant';
	protected static $family = 'performance';

	public static function check() {

		$issues = array();

		// Check 1: Verify widget loading strategy
		$loading_strategy = get_option( 'userway_loading_strategy', '' );
		if ( 'defer' !== $loading_strategy && 'async' !== $loading_strategy ) {
			$issues[] = __( 'Widget loading strategy not optimized', 'wpshadow' );
		}

		// Check 2: Check async loading configuration
		$async_loading = get_option( 'userway_async_loading', false );
		if ( ! $async_loading ) {
			$issues[] = __( 'Async loading not enabled for widget', 'wpshadow' );
		}

		// Check 3: Verify widget caching
		$widget_cache = get_transient( 'userway_widget_cache' );
		if ( false === $widget_cache ) {
			$issues[] = __( 'Widget caching not active', 'wpshadow' );
		}

		// Check 4: Check script size optimization
		$script_minified = get_option( 'userway_script_minified', false );
		if ( ! $script_minified ) {
			$issues[] = __( 'Widget script not minified', 'wpshadow' );
		}

		// Check 5: Verify CDN usage for widget assets
		$cdn_enabled = get_option( 'userway_cdn_enabled', false );
		if ( ! $cdn_enabled ) {
			$issues[] = __( 'CDN not enabled for widget assets', 'wpshadow' );
		}

		// Check 6: Check lazy loading configuration
		$lazy_load = get_option( 'userway_lazy_load', false );
		if ( ! $lazy_load ) {
			$issues[] = __( 'Lazy loading not enabled for widget', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Userway widget performance issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/userway-widget-performance',
			);
		}

		return null;
	}
}
