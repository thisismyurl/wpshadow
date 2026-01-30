<?php
/**
 * Wpengine Cache Configuration Diagnostic
 *
 * Wpengine Cache Configuration needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.997.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpengine Cache Configuration Diagnostic Class
 *
 * @since 1.997.0000
 */
class Diagnostic_WpengineCacheConfiguration extends Diagnostic_Base {

	protected static $slug = 'wpengine-cache-configuration';
	protected static $title = 'Wpengine Cache Configuration';
	protected static $description = 'Wpengine Cache Configuration needs attention';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();
		
		// Check 1: Page caching enabled
		$page_cache = get_option( 'wpengine_page_caching_enabled', 0 );
		if ( ! $page_cache ) {
			$issues[] = 'Page caching not enabled';
		}
		
		// Check 2: Cache exclusions configured
		$exclusions = absint( get_option( 'wpengine_cache_exclusions_count', 0 ) );
		if ( $exclusions <= 0 ) {
			$issues[] = 'Cache exclusion rules not configured';
		}
		
		// Check 3: User cache separation
		$user_cache = get_option( 'wpengine_user_cache_separation_enabled', 0 );
		if ( ! $user_cache ) {
			$issues[] = 'User cache separation not enabled';
		}
		
		// Check 4: Database cache optimization
		$db_cache = get_option( 'wpengine_database_cache_optimized', 0 );
		if ( ! $db_cache ) {
			$issues[] = 'Database cache not optimized';
		}
		
		// Check 5: Cache busting strategy
		$busting = get_option( 'wpengine_cache_busting_strategy_set', 0 );
		if ( ! $busting ) {
			$issues[] = 'Cache busting strategy not configured';
		}
		
		// Check 6: Cache warming
		$warming = get_option( 'wpengine_cache_warming_enabled', 0 );
		if ( ! $warming ) {
			$issues[] = 'Cache warming not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 35;
			$threat_multiplier = 6;
			$max_threat = 65;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d cache configuration issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wpengine-cache-configuration',
			);
		}
		
		return null;
	}
}
