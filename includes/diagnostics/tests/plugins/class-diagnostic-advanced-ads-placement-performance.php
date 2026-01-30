<?php
/**
 * Advanced Ads Placement Performance Diagnostic
 *
 * Ad placements slowing page load.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.291.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Ads Placement Performance Diagnostic Class
 *
 * @since 1.291.0000
 */
class Diagnostic_AdvancedAdsPlacementPerformance extends Diagnostic_Base {

	protected static $slug = 'advanced-ads-placement-performance';
	protected static $title = 'Advanced Ads Placement Performance';
	protected static $description = 'Ad placements slowing page load';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'ADVADS_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Count ad placements
		$placements = get_option( 'advads-ads-placements', array() );
		if ( count( $placements ) > 20 ) {
			$issues[] = sprintf( __( '%d ad placements (DOM manipulation overhead)', 'wpshadow' ), count( $placements ) );
		}
		
		// Check 2: Loading method
		$async_load = get_option( 'advanced-ads-async', 'on' );
		if ( 'off' === $async_load ) {
			$issues[] = __( 'Synchronous ad loading (blocks page render)', 'wpshadow' );
		}
		
		// Check 3: Ad refresh/rotation
		$enable_refresh = get_option( 'advanced-ads-pro-refresh', array() );
		if ( ! empty( $enable_refresh ) ) {
			$refresh_rate = isset( $enable_refresh['refresh_rate'] ) ? (int) $enable_refresh['refresh_rate'] : 30;
			if ( $refresh_rate < 30 ) {
				$issues[] = sprintf( __( 'Ad refresh every %ds (aggressive, impacts performance)', 'wpshadow' ), $refresh_rate );
			}
		}
		
		// Check 4: Lazy loading
		$lazy_load = get_option( 'advanced-ads-lazy-load', 'off' );
		if ( 'off' === $lazy_load && count( $placements ) > 5 ) {
			$issues[] = __( 'Lazy loading disabled (loads all ads immediately)', 'wpshadow' );
		}
		
		// Check 5: Cache-busting
		$cache_busting = get_option( 'advanced-ads-cache-busting', 'on' );
		if ( 'on' === $cache_busting ) {
			$issues[] = __( 'Cache-busting enabled (prevents caching optimization)', 'wpshadow' );
		}
		
		// Check 6: Group ad rotations
		$ad_groups = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s",
				'advanced-ads-groups'
			)
		);
		
		if ( $ad_groups > 10 ) {
			$issues[] = sprintf( __( '%d ad groups (rotation calculation overhead)', 'wpshadow' ), $ad_groups );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of ad placement issues */
				__( 'Advanced Ads has %d placement performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/advanced-ads-placement-performance',
		);
	}
}
