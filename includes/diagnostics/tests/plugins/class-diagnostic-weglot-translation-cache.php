<?php
/**
 * Weglot Translation Cache Diagnostic
 *
 * Weglot Translation Cache misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1157.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Weglot Translation Cache Diagnostic Class
 *
 * @since 1.1157.0000
 */
class Diagnostic_WeglotTranslationCache extends Diagnostic_Base {

	protected static $slug = 'weglot-translation-cache';
	protected static $title = 'Weglot Translation Cache';
	protected static $description = 'Weglot Translation Cache misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WEGLOT_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify translation cache is enabled
		$cache_enabled = get_option( 'weglot_cache', false );
		if ( ! $cache_enabled ) {
			$issues[] = 'Weglot translation cache not enabled';
		}

		// Check 2: Check cache expiration settings
		$cache_expiration = get_option( 'weglot_cache_expiration', 0 );
		if ( $cache_expiration <= 0 || $cache_expiration > 2592000 ) {
			$issues[] = 'Cache expiration not properly configured';
		}

		// Check 3: Verify cache storage method
		$cache_type = get_option( 'weglot_cache_type', 'none' );
		if ( $cache_type === 'none' ) {
			$issues[] = 'No cache storage method configured';
		}

		// Check 4: Check for cache compatibility with other plugins
		if ( defined( 'WP_CACHE' ) && WP_CACHE && $cache_type !== 'none' ) {
			$cache_compat = get_option( 'weglot_cache_compatibility', false );
			if ( ! $cache_compat ) {
				$issues[] = 'Cache compatibility mode not enabled with other caching plugins';
			}
		}

		// Check 5: Verify cache purge on content update
		$auto_purge = get_option( 'weglot_cache_auto_purge', false );
		if ( ! $auto_purge ) {
			$issues[] = 'Automatic cache purge on content update not enabled';
		}

		// Check 6: Check cache size limits
		$cache_size = get_option( 'weglot_cache_size_limit', 0 );
		if ( $cache_enabled && $cache_size <= 0 ) {
			$issues[] = 'No cache size limit configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Weglot translation cache issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/weglot-translation-cache',
			);
		}

		return null;
	}
}
