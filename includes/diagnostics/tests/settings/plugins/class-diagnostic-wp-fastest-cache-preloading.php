<?php
/**
 * Wp Fastest Cache Preloading Diagnostic
 *
 * Wp Fastest Cache Preloading not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.941.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Fastest Cache Preloading Diagnostic Class
 *
 * @since 1.941.0000
 */
class Diagnostic_WpFastestCachePreloading extends Diagnostic_Base {

	protected static $slug = 'wp-fastest-cache-preloading';
	protected static $title = 'Wp Fastest Cache Preloading';
	protected static $description = 'Wp Fastest Cache Preloading not optimized';
	protected static $family = 'performance';

	public static function check() {
		// Check if WP Fastest Cache is installed
		if ( ! class_exists( 'WpFastestCache' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Get options
		$options = get_option( 'WpFastestCache', array() );
		if ( empty( $options ) ) {
			$issues[] = 'settings_unavailable';
			$threat_level += 15;
			return $this->build_finding( $issues, $threat_level );
		}

		// Check if preloading is enabled
		$preload_enabled = isset( $options['wpFastestCachePreload'] ) && $options['wpFastestCachePreload'] === 'on';
		if ( ! $preload_enabled ) {
			$issues[] = 'preloading_disabled';
			$threat_level += 20;
		}

		// Check sitemap for preloading
		$sitemap_url = get_option( 'wpfc_preload_sitemap', '' );
		if ( empty( $sitemap_url ) && $preload_enabled ) {
			$issues[] = 'no_sitemap_configured';
			$threat_level += 15;
		}

		// Check preload interval
		$preload_interval = get_option( 'wpfc_preload_interval', 0 );
		if ( $preload_interval > 10 ) {
			$issues[] = 'preload_interval_too_long';
			$threat_level += 10;
		}

		// Check if homepage is preloaded
		$preload_homepage = isset( $options['wpFastestCachePreload_homepage'] ) && $options['wpFastestCachePreload_homepage'] === 'on';
		if ( ! $preload_homepage && $preload_enabled ) {
			$issues[] = 'homepage_not_preloaded';
			$threat_level += 10;
		}

		// Check timeout settings
		$timeout = get_option( 'wpfc_preload_timeout', 5 );
		if ( $timeout < 3 ) {
			$issues[] = 'timeout_too_short';
			$threat_level += 5;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of preloading issues */
				__( 'WP Fastest Cache preloading has problems: %s. This means users hit uncached pages, causing slower load times and higher server load.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-fastest-cache-preloading',
			);
		}
		
		return null;
	}
}
