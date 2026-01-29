<?php
/**
 * Cache Preload Configuration Diagnostic
 *
 * Validates cache preload settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1810
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cache Preload Config Class
 *
 * Checks cache preload setup.
 *
 * @since 1.5029.1810
 */
class Diagnostic_Cache_Preload_Config extends Diagnostic_Base {

	protected static $slug        = 'cache-preload-config';
	protected static $title       = 'Cache Preload Configuration';
	protected static $description = 'Validates preload settings';
	protected static $family      = 'plugins';

	public static function check() {
		$cache_key = 'wpshadow_cache_preload';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();

		// Check WP Rocket preload.
		if ( function_exists( 'get_rocket_option' ) ) {
			$preload_enabled = get_rocket_option( 'manual_preload', false );
			
			if ( ! $preload_enabled ) {
				$issues[] = 'WP Rocket cache preload disabled';
			}

			$sitemap_preload = get_rocket_option( 'sitemap_preload', false );
			if ( ! $sitemap_preload ) {
				$issues[] = 'Sitemap-based preload not enabled';
			}
		}

		// Check WP Fastest Cache preload.
		if ( class_exists( 'WpFastestCache' ) ) {
			$options = get_option( 'WpFastestCache', array() );
			
			if ( ! isset( $options['wpFastestCachePreload'] ) ) {
				$issues[] = 'WP Fastest Cache preload not configured';
			}
		}

		// Check preload frequency.
		$preload_cron = wp_get_schedule( 'wprocket_preload_cache' );
		if ( false === $preload_cron ) {
			// Check WP Fastest Cache cron.
			$wpfc_cron = wp_get_schedule( 'wpfc_preload_cache' );
			if ( false === $wpfc_cron ) {
				$issues[] = 'No scheduled cache preload found';
			}
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d cache preload configuration issues. Enable preload for better performance.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cache-preload-setup',
				'data'         => array(
					'preload_issues' => $issues,
					'total_issues' => count( $issues ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
