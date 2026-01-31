<?php
/**
 * Beaver Builder Pro Cache Clearing Diagnostic
 *
 * Beaver Builder Pro Cache Clearing issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.804.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Pro Cache Clearing Diagnostic Class
 *
 * @since 1.804.0000
 */
class Diagnostic_BeaverBuilderProCacheClearing extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-pro-cache-clearing';
	protected static $title = 'Beaver Builder Pro Cache Clearing';
	protected static $description = 'Beaver Builder Pro Cache Clearing issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Pro version check.
		$is_pro = defined( 'FL_BUILDER_PRO' ) && FL_BUILDER_PRO;
		if ( ! $is_pro ) {
			return null;
		}

		// Check 2: Advanced cache clearing.
		$advanced_clear = get_option( '_fl_builder_pro_advanced_cache', '1' );
		if ( '0' === $advanced_clear ) {
			$issues[] = 'advanced cache clearing disabled';
		}

		// Check 3: Third-party cache integration.
		$third_party = get_option( '_fl_builder_pro_third_party_cache', '1' );
		if ( '0' === $third_party ) {
			$issues[] = 'third-party cache integration off';
		}

		// Check 4: Global cache purge.
		$global_purge = get_option( '_fl_builder_pro_global_cache_purge', '1' );
		if ( '0' === $global_purge ) {
			$issues[] = 'global cache purge disabled';
		}

		// Check 5: Browser cache busting.
		$cache_bust = get_option( '_fl_builder_pro_cache_busting', '1' );
		if ( '0' === $cache_bust ) {
			$issues[] = 'cache busting disabled';
		}

		// Check 6: CDN purge.
		$cdn_purge = get_option( '_fl_builder_pro_cdn_purge', '0' );
		if ( '0' === $cdn_purge ) {
			$issues[] = 'CDN cache purge disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 50 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Beaver Builder Pro cache issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-pro-cache-clearing',
			);
		}

		return null;
	}
}
