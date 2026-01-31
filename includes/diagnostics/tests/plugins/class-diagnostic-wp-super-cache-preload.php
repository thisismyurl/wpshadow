<?php
/**
 * Wp Super Cache Preload Diagnostic
 *
 * Wp Super Cache Preload not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.895.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Super Cache Preload Diagnostic Class
 *
 * @since 1.895.0000
 */
class Diagnostic_WpSuperCachePreload extends Diagnostic_Base {

	protected static $slug = 'wp-super-cache-preload';
	protected static $title = 'Wp Super Cache Preload';
	protected static $description = 'Wp Super Cache Preload not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'wp_cache_postload' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Preload enabled.
		$preload = get_option( 'wp_super_cache_preload', '0' );
		if ( '0' === $preload ) {
			$issues[] = 'cache preload disabled';
		}

		// Check 2: Preload interval.
		$interval = get_option( 'wp_super_cache_preload_interval', 600 );
		if ( $interval < 300 ) {
			$issues[] = 'preload interval too frequent';
		}

		// Check 3: Preload posts.
		$posts = get_option( 'wp_super_cache_preload_posts', '1' );
		if ( '0' === $posts ) {
			$issues[] = 'post preloading disabled';
		}

		// Check 4: Preload pages.
		$pages = get_option( 'wp_super_cache_preload_pages', '1' );
		if ( '0' === $pages ) {
			$issues[] = 'page preloading disabled';
		}

		// Check 5: Notification on completion.
		$notify = get_option( 'wp_super_cache_preload_notify', '0' );
		if ( '0' === $notify ) {
			$issues[] = 'completion notifications disabled';
		}

		// Check 6: Taxonomies preload.
		$taxonomies = get_option( 'wp_super_cache_preload_taxonomies', '0' );
		if ( '0' === $taxonomies ) {
			$issues[] = 'taxonomy preloading disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 50 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'WP Super Cache preload issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-super-cache-preload',
			);
		}

		return null;
	}
}
