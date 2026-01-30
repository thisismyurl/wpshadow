<?php
/**
 * Wp Super Cache Expert Settings Diagnostic
 *
 * Wp Super Cache Expert Settings not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.898.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Super Cache Expert Settings Diagnostic Class
 *
 * @since 1.898.0000
 */
class Diagnostic_WpSuperCacheExpertSettings extends Diagnostic_Base {

	protected static $slug = 'wp-super-cache-expert-settings';
	protected static $title = 'Wp Super Cache Expert Settings';
	protected static $description = 'Wp Super Cache Expert Settings not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'wp_cache_postload' ) ) {
			return null;
		}
		
		global $wp_cache_mod_rewrite, $wp_cache_mobile_enabled, $cache_compression;
		$issues = array();
		$threat_level = 0;

		// Check caching mode
		if ( ! isset( $wp_cache_mod_rewrite ) || ! $wp_cache_mod_rewrite ) {
			$issues[] = 'mod_rewrite_not_enabled';
			$threat_level += 15;
		}

		// Check compression
		if ( ! isset( $cache_compression ) || ! $cache_compression ) {
			$issues[] = 'compression_disabled';
			$threat_level += 10;
		}

		// Check cache location
		$cache_path = WP_CONTENT_DIR . '/cache/';
		if ( ! is_dir( $cache_path ) || ! is_writable( $cache_path ) ) {
			$issues[] = 'cache_directory_not_writable';
			$threat_level += 20;
		}

		// Check cache rebuild
		$cache_rebuild = get_option( 'wp_cache_rebuild_files', 0 );
		if ( ! $cache_rebuild ) {
			$issues[] = 'cache_rebuild_disabled';
			$threat_level += 10;
		}

		// Check garbage collection
		$cache_gc = get_option( 'wp_cache_gc_enabled', 0 );
		if ( ! $cache_gc ) {
			$issues[] = 'garbage_collection_disabled';
			$threat_level += 10;
		}

		// Check mobile caching
		if ( ! isset( $wp_cache_mobile_enabled ) || ! $wp_cache_mobile_enabled ) {
			$issues[] = 'mobile_caching_disabled';
			$threat_level += 15;
		}

		// Check CDN support
		$cdn_enabled = get_option( 'ossdl_off_cdn_url', '' );
		if ( empty( $cdn_enabled ) ) {
			$issues[] = 'cdn_not_configured';
			$threat_level += 5;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of expert setting issues */
				__( 'WP Super Cache expert settings need optimization: %s. This reduces caching efficiency and misses performance opportunities.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/wp-super-cache-expert-settings',
			);
		}
		
		return null;
	}
}
