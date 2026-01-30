<?php
/**
 * Social Warfare Share Counts Diagnostic
 *
 * Social Warfare share counts slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.431.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Warfare Share Counts Diagnostic Class
 *
 * @since 1.431.0000
 */
class Diagnostic_SocialWarfareShareCounts extends Diagnostic_Base {

	protected static $slug = 'social-warfare-share-counts';
	protected static $title = 'Social Warfare Share Counts';
	protected static $description = 'Social Warfare share counts slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'SWP_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Share counts enabled
		$counts = get_option( 'swp_share_counts_enabled', 0 );
		if ( ! $counts ) {
			$issues[] = 'Share counts not enabled';
		}
		
		// Check 2: Cache enabled
		$cache = get_option( 'swp_share_counts_cache', 0 );
		if ( ! $cache ) {
			$issues[] = 'Share count caching not enabled';
		}
		
		// Check 3: Cache expiration set
		$cache_time = absint( get_option( 'swp_share_count_cache_hours', 0 ) );
		if ( $cache_time <= 0 ) {
			$issues[] = 'Cache expiration time not configured';
		}
		
		// Check 4: API optimization
		$api_opt = get_option( 'swp_api_optimization_enabled', 0 );
		if ( ! $api_opt ) {
			$issues[] = 'API optimization not enabled';
		}
		
		// Check 5: Background refresh
		$bg_refresh = get_option( 'swp_background_refresh_enabled', 0 );
		if ( ! $bg_refresh ) {
			$issues[] = 'Background refresh not enabled';
		}
		
		// Check 6: Rate limiting
		$rate_limit = get_option( 'swp_api_rate_limiting', 0 );
		if ( ! $rate_limit ) {
			$issues[] = 'API rate limiting not configured';
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
					'Found %d share count issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/social-warfare-share-counts',
			);
		}
		
		return null;
	}
}
