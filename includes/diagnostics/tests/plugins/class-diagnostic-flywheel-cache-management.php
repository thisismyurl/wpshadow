<?php
/**
 * Flywheel Cache Management Diagnostic
 *
 * Flywheel Cache Management needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1005.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flywheel Cache Management Diagnostic Class
 *
 * @since 1.1005.0000
 */
class Diagnostic_FlywheelCacheManagement extends Diagnostic_Base {

	protected static $slug = 'flywheel-cache-management';
	protected static $title = 'Flywheel Cache Management';
	protected static $description = 'Flywheel Cache Management needs attention';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();
		
		// Check 1: Flywheel cache enabled
		$cache_enabled = get_option( 'flywheel_cache_enabled', false );
		if ( ! $cache_enabled ) {
			$issues[] = 'Flywheel cache disabled';
		}
		
		// Check 2: Cache purge strategy configured
		$purge_strategy = get_option( 'flywheel_cache_purge_strategy', '' );
		if ( empty( $purge_strategy ) ) {
			$issues[] = 'Cache purge strategy not configured';
		}
		
		// Check 3: Cache exclusions defined
		$exclusions = get_option( 'flywheel_cache_exclusions', array() );
		if ( empty( $exclusions ) ) {
			$issues[] = 'No cache exclusions defined';
		}
		
		// Check 4: CDN sync enabled
		$cdn_sync = get_option( 'flywheel_cdn_sync_enabled', false );
		if ( ! $cdn_sync ) {
			$issues[] = 'CDN sync disabled';
		}
		
		// Check 5: Auto-purge on content update
		$auto_purge = get_option( 'flywheel_auto_purge_enabled', false );
		if ( ! $auto_purge ) {
			$issues[] = 'Auto-purge disabled';
		}
		
		// Check 6: Cache warmup configured
		$cache_warmup = get_option( 'flywheel_cache_warmup_enabled', false );
		if ( ! $cache_warmup ) {
			$issues[] = 'Cache warmup not configured';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Flywheel cache management issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/flywheel-cache-management',
			);
		}
		
		return null;
	}
}
