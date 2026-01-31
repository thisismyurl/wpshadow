<?php
/**
 * Runcloud Cache Rules Diagnostic
 *
 * Runcloud Cache Rules needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1024.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runcloud Cache Rules Diagnostic Class
 *
 * @since 1.1024.0000
 */
class Diagnostic_RuncloudCacheRules extends Diagnostic_Base {

	protected static $slug = 'runcloud-cache-rules';
	protected static $title = 'Runcloud Cache Rules';
	protected static $description = 'Runcloud Cache Rules needs attention';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'RUNCLOUD_CACHE_VERSION' ) && ! get_option( 'runcloud_cache_enabled', false ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify cache is enabled
		$cache_enabled = get_option( 'runcloud_cache_enabled', 0 );
		if ( ! $cache_enabled ) {
			$issues[] = 'RunCloud cache not enabled';
		}

		// Check 2: Check for cache rules configured
		$cache_rules = get_option( 'runcloud_cache_rules', array() );
		if ( empty( $cache_rules ) ) {
			$issues[] = 'No cache rules configured';
		}

		// Check 3: Verify cache exclusions
		$cache_exclusions = get_option( 'runcloud_cache_exclusions', array() );
		if ( empty( $cache_exclusions ) ) {
			$issues[] = 'Cache exclusions not configured';
		}

		// Check 4: Check for cache purge hooks
		$purge_hooks = get_option( 'runcloud_cache_purge_hooks', 0 );
		if ( ! $purge_hooks ) {
			$issues[] = 'Cache purge hooks not enabled';
		}

		// Check 5: Verify browser cache
		$browser_cache = get_option( 'runcloud_browser_cache', 0 );
		if ( ! $browser_cache ) {
			$issues[] = 'Browser cache not enabled';
		}

		// Check 6: Check for page cache TTL
		$cache_ttl = get_option( 'runcloud_cache_ttl', 0 );
		if ( $cache_ttl <= 0 ) {
			$issues[] = 'Cache TTL not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d RunCloud cache rule issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/runcloud-cache-rules',
			);
		}

		return null;
	}
}
