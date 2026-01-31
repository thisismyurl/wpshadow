<?php
/**
 * Beaver Builder Cache Clearing Diagnostic
 *
 * Beaver Builder cache not clearing properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.340.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Cache Clearing Diagnostic Class
 *
 * @since 1.340.0000
 */
class Diagnostic_BeaverBuilderCacheClearing extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-cache-clearing';
	protected static $title = 'Beaver Builder Cache Clearing';
	protected static $description = 'Beaver Builder cache not clearing properly';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Auto clear cache.
		$auto_clear = get_option( '_fl_builder_auto_clear_cache', '1' );
		if ( '0' === $auto_clear ) {
			$issues[] = 'auto cache clearing disabled';
		}

		// Check 2: Cache directory writable.
		$cache_dir = get_option( '_fl_builder_cache_dir', '' );
		if ( ! empty( $cache_dir ) && ! is_writable( $cache_dir ) ) {
			$issues[] = 'cache directory not writable';
		}

		// Check 3: Clear on save.
		$clear_on_save = get_option( '_fl_builder_clear_cache_on_save', '1' );
		if ( '0' === $clear_on_save ) {
			$issues[] = 'cache not cleared on save';
		}

		// Check 4: Global cache enabled.
		$global_cache = get_option( '_fl_builder_enable_cache', '1' );
		if ( '0' === $global_cache ) {
			$issues[] = 'global caching disabled';
		}

		// Check 5: Cache timeout.
		$cache_timeout = get_option( '_fl_builder_cache_timeout', 3600 );
		if ( $cache_timeout > 86400 ) {
			$issues[] = 'cache timeout too long';
		}

		// Check 6: Debug mode.
		if ( defined( 'FL_BUILDER_DEBUG' ) && FL_BUILDER_DEBUG ) {
			$issues[] = 'debug mode enabled (disables cache)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 50 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Beaver Builder cache issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-cache-clearing',
			);
		}

		return null;
	}
}
