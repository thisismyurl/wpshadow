<?php
/**
 * Advanced Ads Auto Refresh Diagnostic
 *
 * Ad refresh consuming resources.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.295.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Ads Auto Refresh Diagnostic Class
 *
 * @since 1.295.0000
 */
class Diagnostic_AdvancedAdsRefreshOptimization extends Diagnostic_Base {

	protected static $slug = 'advanced-ads-refresh-optimization';
	protected static $title = 'Advanced Ads Auto Refresh';
	protected static $description = 'Ad refresh consuming resources';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'ADVADS_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Auto-refresh enabled.
		global $wpdb;
		$auto_refresh_ads = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s",
				'_advads_ad_settings',
				'%auto_refresh%:%1%'
			)
		);
		if ( $auto_refresh_ads === 0 ) {
			return null; // No auto-refresh ads, no issues to check.
		}

		// Check 2: Refresh interval too frequent.
		$refresh_interval = get_option( 'advads_refresh_interval', 30 );
		if ( $refresh_interval < 30 ) {
			$issues[] = "refresh interval set to {$refresh_interval}s (under 30s may violate ad policies)";
		}

		// Check 3: User inactive detection.
		$inactive_refresh = get_option( 'advads_pause_on_inactive', '0' );
		if ( '0' === $inactive_refresh && $auto_refresh_ads > 0 ) {
			$issues[] = 'ads refresh when user inactive (wastes impressions and bandwidth)';
		}

		// Check 4: Viewport visibility check.
		$viewport_check = get_option( 'advads_refresh_viewport_only', '0' );
		if ( '0' === $viewport_check && $auto_refresh_ads > 0 ) {
			$issues[] = 'ads refresh when not in viewport (wasted requests)';
		}

		// Check 5: Maximum refresh count.
		$max_refreshes = get_option( 'advads_max_refresh_count', 0 );
		if ( 0 === $max_refreshes && $auto_refresh_ads > 0 ) {
			$issues[] = 'no maximum refresh count (unlimited requests per page)';
		}

		// Check 6: Mobile refresh settings.
		$mobile_refresh = get_option( 'advads_mobile_refresh', 'same' );
		if ( 'same' === $mobile_refresh && $auto_refresh_ads > 0 ) {
			$issues[] = 'same refresh settings for mobile (consider longer intervals on mobile)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Advanced Ads refresh optimization issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-ads-refresh-optimization',
			);
		}

		return null;
	}
}
