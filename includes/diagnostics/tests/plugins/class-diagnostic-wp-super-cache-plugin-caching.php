<?php
/**
 * Wp Super Cache Plugin Caching Diagnostic
 *
 * Wp Super Cache Plugin Caching not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.899.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Super Cache Plugin Caching Diagnostic Class
 *
 * @since 1.899.0000
 */
class Diagnostic_WpSuperCachePluginCaching extends Diagnostic_Base {

	protected static $slug = 'wp-super-cache-plugin-caching';
	protected static $title = 'Wp Super Cache Plugin Caching';
	protected static $description = 'Wp Super Cache Plugin Caching not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'wp_cache_postload' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Caching enabled
		$cache_enabled = get_option( 'wp_super_cache_enabled', 0 );
		if ( ! $cache_enabled ) {
			$issues[] = __( 'Caching disabled (no performance benefit)', 'wpshadow' );
		}

		// Check 2: Cache mode
		$cache_mode = get_option( 'wp_cache_mod_rewrite', 0 );
		if ( ! $cache_mode ) {
			$issues[] = __( 'Not using mod_rewrite (slower caching)', 'wpshadow' );
		}

		// Check 3: Compression
		$compression = get_option( 'wp_cache_compression', 0 );
		if ( ! $compression ) {
			$issues[] = __( 'Compression disabled (larger cache files)', 'wpshadow' );
		}

		// Check 4: Cache expiry
		$cache_timeout = get_option( 'wp_cache_timeout', 3600 );
		if ( $cache_timeout > 86400 ) {
			$issues[] = __( 'Long cache timeout (stale content)', 'wpshadow' );
		}

		// Check 5: Mobile caching
		$mobile_cache = get_option( 'wp_cache_mobile_enabled', 0 );
		if ( ! $mobile_cache ) {
			$issues[] = __( 'Mobile caching disabled (slower mobile)', 'wpshadow' );
		}

		// Check 6: Preload
		$preload = get_option( 'wp_cache_preload', 0 );
		if ( ! $preload ) {
			$issues[] = __( 'Cache preload disabled (first hit penalty)', 'wpshadow' );
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
				__( 'WP Super Cache has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-super-cache-plugin-caching',
		);
	}
}
