<?php
/**
 * Advanced Ads AdSense Integration Diagnostic
 *
 * AdSense integration misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.297.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Ads AdSense Integration Diagnostic Class
 *
 * @since 1.297.0000
 */
class Diagnostic_AdvancedAdsAdsenseIntegration extends Diagnostic_Base {

	protected static $slug = 'advanced-ads-adsense-integration';
	protected static $title = 'Advanced Ads AdSense Integration';
	protected static $description = 'AdSense integration misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'ADVADS_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: AdSense publisher ID configured.
		$publisher_id = get_option( 'advads_adsense_publisher_id', '' );
		if ( empty( $publisher_id ) ) {
			$issues[] = 'AdSense publisher ID not configured';
		} elseif ( ! preg_match( '/^ca-pub-[0-9]{16}$/', $publisher_id ) ) {
			$issues[] = 'invalid AdSense publisher ID format';
		}
		
		// Check 2: Auto ads enabled without proper configuration.
		$auto_ads = get_option( 'advads_adsense_auto_ads', '0' );
		if ( '1' === $auto_ads && empty( $publisher_id ) ) {
			$issues[] = 'auto ads enabled but publisher ID missing';
		}
		
		// Check 3: Ad balance settings.
		$ad_balance = get_option( 'advads_adsense_ad_balance', 'default' );
		if ( 'default' === $ad_balance ) {
			$issues[] = 'ad balance not optimized (using AdSense default settings)';
		}
		
		// Check 4: Responsive ad units.
		global $wpdb;
		$fixed_size_ads = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_content LIKE %s AND post_content NOT LIKE %s",
				'advanced_ads',
				'%adsense%',
				'%responsive%'
			)
		);
		if ( $fixed_size_ads > 0 ) {
			$issues[] = "{$fixed_size_ads} AdSense ads using fixed sizes (use responsive for mobile)";
		}
		
		// Check 5: Page-level ads configuration.
		$page_level_ads = get_option( 'advads_adsense_page_level_ads', '0' );
		$anchor_ads = get_option( 'advads_adsense_anchor_ads', '0' );
		if ( '0' === $page_level_ads && '0' === $anchor_ads ) {
			$issues[] = 'page-level and anchor ads disabled (missing revenue opportunities)';
		}
		
		// Check 6: AdSense limit per page.
		$ads_per_page = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_content LIKE %s AND post_status = %s",
				'advanced_ads',
				'%adsense%',
				'publish'
			)
		);
		if ( $ads_per_page > 3 ) {
			$issues[] = "{$ads_per_page} AdSense ads configured (AdSense policy limits ads per page)";
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Advanced Ads AdSense integration issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-ads-adsense-integration',
			);
		}
		
		return null;
	}
}
