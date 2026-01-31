<?php
/**
 * Cache Plugin Conflicts Diagnostic
 *
 * Detects multiple cache plugins running simultaneously.
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
 * Cache Plugin Conflicts Class
 *
 * Checks for multiple active cache plugins.
 *
 * @since 1.5029.1810
 */
class Diagnostic_Cache_Plugin_Conflicts extends Diagnostic_Base {

	protected static $slug        = 'cache-plugin-conflicts';
	protected static $title       = 'Cache Plugin Conflicts';
	protected static $description = 'Detects multiple cache plugins';
	protected static $family      = 'plugins';

	public static function check() {
		$cache_key = 'wpshadow_cache_conflicts';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$active_cache_plugins = array();
		
		$cache_plugins = array(
			'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',
			'wp-super-cache/wp-cache.php' => 'WP Super Cache',
			'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
			'wp-rocket/wp-rocket.php' => 'WP Rocket',
			'cache-enabler/cache-enabler.php' => 'Cache Enabler',
			'comet-cache/comet-cache.php' => 'Comet Cache',
			'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
			'autoptimize/autoptimize.php' => 'Autoptimize',
			'sg-cachepress/sg-cachepress.php' => 'SiteGround Optimizer',
		);

		foreach ( $cache_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_cache_plugins[] = $plugin_name;
			}
		}

		if ( count( $active_cache_plugins ) > 1 ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d cache plugins active simultaneously! This causes conflicts and reduced performance.', 'wpshadow' ),
					count( $active_cache_plugins )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cache-plugin-conflicts',
				'data'         => array(
					'active_plugins' => $active_cache_plugins,
					'total_active' => count( $active_cache_plugins ),
					'recommendation' => 'Keep only one cache plugin active',
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
