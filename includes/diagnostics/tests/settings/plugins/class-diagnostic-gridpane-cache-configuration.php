<?php
/**
 * Gridpane Cache Configuration Diagnostic
 *
 * Gridpane Cache Configuration needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1027.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gridpane Cache Configuration Diagnostic Class
 *
 * @since 1.1027.0000
 */
class Diagnostic_GridpaneCacheConfiguration extends Diagnostic_Base {

	protected static $slug = 'gridpane-cache-configuration';
	protected static $title = 'Gridpane Cache Configuration';
	protected static $description = 'Gridpane Cache Configuration needs attention';
	protected static $family = 'performance';

	public static function check() {
		// Check if running on GridPane
		$is_gridpane = defined( 'GRIDPANE' ) || file_exists( '/etc/nginx/sites-available/gridpane.conf' );
		if ( ! $is_gridpane ) {
			return null; // Not on GridPane
		}

		$issues = array();
		$threat_level = 0;

		// Check for conflicting cache plugins
		$conflicting_plugins = array(
			'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
			'wp-super-cache/wp-super-cache.php' => 'WP Super Cache',
			'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',
		);

		foreach ( $conflicting_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$issues[] = 'conflicting_cache_plugin_' . sanitize_key( $plugin_name );
				$threat_level += 20;
			}
		}

		// Check Nginx cache directory
		$nginx_cache_path = '/var/www/cache';
		if ( ! is_dir( $nginx_cache_path ) || ! is_writable( $nginx_cache_path ) ) {
			$issues[] = 'nginx_cache_not_writable';
			$threat_level += 15;
		}

		// Check Redis availability
		if ( ! class_exists( 'Redis' ) ) {
			$issues[] = 'redis_unavailable';
			$threat_level += 10;
		}

		// Check cache purge configuration
		$purge_enabled = get_option( 'gridpane_cache_purge_on_update', true );
		if ( ! $purge_enabled ) {
			$issues[] = 'auto_purge_disabled';
			$threat_level += 10;
		}

		// Check object cache drop-in
		$object_cache = WP_CONTENT_DIR . '/object-cache.php';
		if ( ! file_exists( $object_cache ) ) {
			$issues[] = 'object_cache_dropin_missing';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of cache configuration issues */
				__( 'GridPane cache configuration has problems: %s. This prevents optimal use of GridPane\'s Nginx caching and Redis object cache.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/gridpane-cache-configuration',
			);
		}
		
		return null;
	}
}
