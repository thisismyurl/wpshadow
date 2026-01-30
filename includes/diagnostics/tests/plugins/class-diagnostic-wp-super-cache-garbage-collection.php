<?php
/**
 * Wp Super Cache Garbage Collection Diagnostic
 *
 * Wp Super Cache Garbage Collection not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.894.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Super Cache Garbage Collection Diagnostic Class
 *
 * @since 1.894.0000
 */
class Diagnostic_WpSuperCacheGarbageCollection extends Diagnostic_Base {

	protected static $slug = 'wp-super-cache-garbage-collection';
	protected static $title = 'Wp Super Cache Garbage Collection';
	protected static $description = 'Wp Super Cache Garbage Collection not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'wp_cache_postload' ) ) {
			return null;
		}
		
		global $wp_cache_config_file;
		$issues = array();
		
		// Check 1: Garbage collection enabled
		$gc_enabled = get_option( 'wp_super_cache_gc_enabled', 'no' );
		if ( 'no' === $gc_enabled ) {
			$issues[] = __( 'Garbage collection disabled (cache bloat)', 'wpshadow' );
		}
		
		// Check 2: GC schedule
		$gc_schedule = wp_get_schedule( 'wp_cache_gc' );
		if ( false === $gc_schedule ) {
			$issues[] = __( 'No GC schedule (old cache never deleted)', 'wpshadow' );
		}
		
		// Check 3: Cache size
		$cache_dir = WP_CONTENT_DIR . '/cache/';
		if ( is_dir( $cache_dir ) ) {
			$size = 0;
			$files = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $cache_dir ),
				\RecursiveIteratorIterator::LEAVES_ONLY
			);
			foreach ( $files as $file ) {
				if ( $file->isFile() ) {
					$size += $file->getSize();
				}
			}
			$size_mb = $size / 1024 / 1024;
			if ( $size_mb > 500 ) {
				$issues[] = sprintf( __( '%d MB cache (excessive)', 'wpshadow' ), round( $size_mb ) );
			}
		}
		
		// Check 4: Max age
		$max_age = get_option( 'wp_super_cache_max_age', 0 );
		if ( $max_age === 0 ) {
			$issues[] = __( 'No cache expiration (stale content)', 'wpshadow' );
		}
		
		// Check 5: Delete expired files
		$delete_expired = get_option( 'wp_super_cache_delete_expired', 'no' );
		if ( 'no' === $delete_expired ) {
			$issues[] = __( 'Expired files not deleted (disk space)', 'wpshadow' );
		}
		
		// Check 6: GC timeout
		$gc_timeout = get_option( 'wp_super_cache_gc_timeout', 600 );
		if ( $gc_timeout > 1800 ) {
			$issues[] = __( 'GC timeout too long (resource intensive)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'WP Super Cache garbage collection has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-super-cache-garbage-collection',
		);
	}
}
