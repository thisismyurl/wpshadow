<?php
/**
 * Advanced Ads Content Insertion Diagnostic
 *
 * Content injection breaking layout.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.296.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Ads Content Insertion Diagnostic Class
 *
 * @since 1.296.0000
 */
class Diagnostic_AdvancedAdsContentInsertion extends Diagnostic_Base {

	protected static $slug = 'advanced-ads-content-insertion';
	protected static $title = 'Advanced Ads Content Insertion';
	protected static $description = 'Content injection breaking layout';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'ADVADS_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Auto-injection enabled.
		global $wpdb;
		$auto_inject_ads = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s",
				'_advads_ad_settings',
				'%auto_inject%'
			)
		);
		if ( $auto_inject_ads === 0 ) {
			$issues[] = 'no ads configured for automatic content injection';
		}
		
		// Check 2: Paragraph position conflicts.
		$paragraph_ads = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s",
				'_advads_ad_settings',
				'%paragraph%:%0%'
			)
		);
		if ( $paragraph_ads > 1 ) {
			$issues[] = "{$paragraph_ads} ads targeting same paragraph position (may overlap)";
		}
		
		// Check 3: Content length requirements.
		$min_content_length = get_option( 'advads_min_content_length', 0 );
		if ( 0 === $min_content_length ) {
			$issues[] = 'no minimum content length set (ads may display on thin content)';
		}
		
		// Check 4: Ad injection on short posts.
		$short_posts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s AND LENGTH(post_content) < 500",
				'post',
				'publish'
			)
		);
		if ( $short_posts > 0 && 0 === $min_content_length && $auto_inject_ads > 0 ) {
			$issues[] = "{$short_posts} short posts may have injected ads (layout issues)";
		}
		
		// Check 5: Mobile responsiveness.
		$mobile_settings = get_option( 'advads_mobile_injection', 'same' );
		if ( 'same' === $mobile_settings ) {
			$issues[] = 'same ad injection used for mobile (consider mobile-specific placements)';
		}
		
		// Check 6: Ad spacing configuration.
		$ad_spacing = get_option( 'advads_content_ad_spacing', 0 );
		if ( 0 === $ad_spacing && $auto_inject_ads > 1 ) {
			$issues[] = 'no spacing configured between ads (may violate ad network policies)';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Advanced Ads content insertion issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-ads-content-insertion',
			);
		}
		
		return null;
	}
}
