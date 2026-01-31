<?php
/**
 * WP Fastest Cache Configuration Diagnostic
 *
 * Validates WP Fastest Cache settings.
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
 * WP Fastest Cache Config Class
 *
 * Checks cache configuration.
 *
 * @since 1.5029.1810
 */
class Diagnostic_WP_Fastest_Cache_Config extends Diagnostic_Base {

	protected static $slug        = 'wp-fastest-cache-config';
	protected static $title       = 'WP Fastest Cache Configuration';
	protected static $description = 'Validates cache settings';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! class_exists( 'WpFastestCache' ) ) {
			return null;
		}

		$cache_key = 'wpshadow_wpfc_config';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();
		$options = get_option( 'WpFastestCache', array() );

		// Check if cache is enabled.
		if ( empty( $options ) || ! isset( $options['wpFastestCacheStatus'] ) ) {
			$issues[] = 'Cache is not enabled';
		}

		// Check mobile cache.
		if ( ! isset( $options['wpFastestCacheMobileTheme'] ) ) {
			$issues[] = 'Mobile cache not configured';
		}

		// Check preload.
		if ( ! isset( $options['wpFastestCachePreload'] ) ) {
			$issues[] = 'Cache preload not enabled';
		}

		// Check minification.
		if ( ! isset( $options['wpFastestCacheMinifyHtml'] ) ) {
			$issues[] = 'HTML minification not enabled';
		}

		// Check Gzip.
		if ( ! isset( $options['wpFastestCacheGzip'] ) ) {
			$issues[] = 'Gzip compression not enabled';
		}

		// Check browser caching.
		if ( ! isset( $options['wpFastestCacheLBC'] ) ) {
			$issues[] = 'Browser caching not configured';
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d WP Fastest Cache configuration issues. Optimize settings for better performance.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp-fastest-cache-config',
				'data'         => array(
					'config_issues' => $issues,
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
