<?php
/**
 * Advanced Ads Lazy Loading Diagnostic
 *
 * Ads not lazy loaded properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.294.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Ads Lazy Loading Diagnostic Class
 *
 * @since 1.294.0000
 */
class Diagnostic_AdvancedAdsLazyLoading extends Diagnostic_Base {

	protected static $slug = 'advanced-ads-lazy-loading';
	protected static $title = 'Advanced Ads Lazy Loading';
	protected static $description = 'Ads not lazy loaded properly';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'ADVADS_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Lazy loading enabled.
		$lazy_loading = get_option( 'advads_lazy_loading', '0' );
		if ( '0' === $lazy_loading ) {
			$issues[] = 'lazy loading disabled (ads loaded immediately on page load)';
		}

		// Check 2: Lazy load offset configuration.
		$lazy_offset = get_option( 'advads_lazy_offset', 0 );
		if ( 0 === $lazy_offset && '1' === $lazy_loading ) {
			$issues[] = 'lazy load offset not configured (ads load at viewport edge)';
		}

		// Check 3: Above-the-fold ads.
		global $wpdb;
		$above_fold_ads = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s",
				'_advads_ad_settings',
				'%position%:%top%'
			)
		);
		if ( $above_fold_ads > 0 && '1' === $lazy_loading ) {
			$issues[] = "{$above_fold_ads} above-the-fold ads with lazy loading (may reduce revenue)";
		}

		// Check 4: Mobile lazy loading.
		$mobile_lazy_load = get_option( 'advads_mobile_lazy_load', 'same' );
		if ( 'same' === $mobile_lazy_load ) {
			$issues[] = 'same lazy load settings for mobile (consider more aggressive mobile lazy loading)';
		}

		// Check 5: IntersectionObserver support.
		$use_intersection = get_option( 'advads_use_intersection_observer', '1' );
		if ( '0' === $use_intersection && '1' === $lazy_loading ) {
			$issues[] = 'IntersectionObserver disabled (using less efficient scroll events)';
		}

		// Check 6: Placeholder configuration.
		$placeholder_enabled = get_option( 'advads_lazy_placeholder', '0' );
		if ( '0' === $placeholder_enabled && '1' === $lazy_loading ) {
			$issues[] = 'lazy load placeholders disabled (may cause layout shift)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Advanced Ads lazy loading issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-ads-lazy-loading',
			);
		}

		return null;
	}
}
