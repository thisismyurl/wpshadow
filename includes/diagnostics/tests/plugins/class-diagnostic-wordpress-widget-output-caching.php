<?php
/**
 * Wordpress Widget Output Caching Diagnostic
 *
 * Wordpress Widget Output Caching issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1285.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Widget Output Caching Diagnostic Class
 *
 * @since 1.1285.0000
 */
class Diagnostic_WordpressWidgetOutputCaching extends Diagnostic_Base {

	protected static $slug = 'wordpress-widget-output-caching';
	protected static $title = 'Wordpress Widget Output Caching';
	protected static $description = 'Wordpress Widget Output Caching issue detected';
	protected static $family = 'performance';

	public static function check() {
		global $wp_registered_widgets, $wpdb;
		$issues = array();
		
		// Check 1: Widget caching enabled
		$widget_cache_enabled = get_option( 'widget_cache', 'off' );
		if ( 'off' === $widget_cache_enabled && count( $wp_registered_widgets ) > 10 ) {
			$issues[] = sprintf( __( '%d widgets without caching (repeated queries)', 'wpshadow' ), count( $wp_registered_widgets ) );
		}
		
		// Check 2: Widget transient expiration
		$cached_widgets = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM {$wpdb->options} 
				WHERE option_name LIKE %s",
				'_transient_widget_%'
			)
		);
		
		$long_cache = 0;
		foreach ( $cached_widgets as $cached ) {
			$expiry_key = str_replace( '_transient_', '_transient_timeout_', $cached->option_name );
			$expiry = get_option( $expiry_key, 0 );
			
			if ( $expiry > 0 && ( $expiry - time() ) > ( 24 * HOUR_IN_SECONDS ) ) {
				$long_cache++;
			}
		}
		
		if ( $long_cache > 5 ) {
			$issues[] = sprintf( __( '%d widgets cached >24h (stale content risk)', 'wpshadow' ), $long_cache );
		}
		
		// Check 3: Dynamic widgets not cached
		$dynamic_types = array( 'calendar', 'recent-posts', 'recent-comments', 'rss' );
		$uncached_dynamic = 0;
		
		foreach ( $wp_registered_widgets as $widget ) {
			if ( isset( $widget['callback'] ) && is_array( $widget['callback'] ) ) {
				$widget_obj = $widget['callback'][0] ?? null;
				if ( $widget_obj && isset( $widget_obj->id_base ) ) {
					if ( in_array( $widget_obj->id_base, $dynamic_types, true ) ) {
						$cache_key = 'widget_' . $widget_obj->id_base;
						if ( ! get_transient( $cache_key ) ) {
							$uncached_dynamic++;
						}
					}
				}
			}
		}
		
		if ( $uncached_dynamic > 0 ) {
			$issues[] = sprintf( __( '%d dynamic widgets uncached (database queries)', 'wpshadow' ), $uncached_dynamic );
		}
		
		// Check 4: Sidebar caching
		$sidebars = wp_get_sidebars_widgets();
		$large_sidebars = 0;
		
		foreach ( $sidebars as $sidebar => $widgets ) {
			if ( is_array( $widgets ) && count( $widgets ) > 8 ) {
				$large_sidebars++;
			}
		}
		
		if ( $large_sidebars > 0 && 'off' === $widget_cache_enabled ) {
			$issues[] = sprintf( __( '%d sidebars with >8 widgets (performance bottleneck)', 'wpshadow' ), $large_sidebars );
		}
		
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = (40 + min(35, count($issues) * 8));
		if ( count( $issues ) >= 3 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		} elseif ( count( $issues ) >= 2 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of widget caching issues */
				__( 'WordPress widget output has %d caching issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wordpress-widget-output-caching',
		);
	}
}
