<?php
/**
 * OptinMonster Mobile Optimization Diagnostic
 *
 * OptinMonster not optimized for mobile devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.221.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OptinMonster Mobile Optimization Diagnostic Class
 *
 * @since 1.221.0000
 */
class Diagnostic_OptinmonsterMobileOptimization extends Diagnostic_Base {

	protected static $slug = 'optinmonster-mobile-optimization';
	protected static $title = 'OptinMonster Mobile Optimization';
	protected static $description = 'OptinMonster not optimized for mobile devices';
	protected static $family = 'performance';

	public static function check() {
		// Check for OptinMonster
		if ( ! defined( 'OMAPI_VERSION' ) && ! class_exists( 'OMAPI' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Mobile campaigns
		$campaigns = get_option( 'optin_monster_campaigns', array() );
		if ( empty( $campaigns ) ) {
			return null;
		}
		
		$mobile_optimized = 0;
		$desktop_only = 0;
		
		foreach ( $campaigns as $campaign ) {
			if ( isset( $campaign['mobile'] ) && $campaign['mobile'] ) {
				$mobile_optimized++;
			} else {
				$desktop_only++;
			}
		}
		
		if ( $desktop_only > 0 ) {
			$issues[] = sprintf( __( '%d campaigns not mobile-optimized', 'wpshadow' ), $desktop_only );
		}
		
		// Check 2: Mobile targeting
		$mobile_targeting = get_option( 'omapi_mobile_targeting', 'no' );
		if ( 'no' === $mobile_targeting ) {
			$issues[] = __( 'Device targeting disabled (same campaigns on all devices)', 'wpshadow' );
		}
		
		// Check 3: Load performance
		$async_load = get_option( 'omapi_async_load', 'yes' );
		if ( 'no' === $async_load ) {
			$issues[] = __( 'Synchronous loading (blocks mobile rendering)', 'wpshadow' );
		}
		
		// Check 4: Mobile conversion tracking
		$track_mobile = get_option( 'omapi_track_mobile_conversions', 'yes' );
		if ( 'no' === $track_mobile ) {
			$issues[] = __( 'Mobile conversions not tracked separately', 'wpshadow' );
		}
		
		// Check 5: Inline campaigns on mobile
		$inline_mobile = get_option( 'omapi_inline_mobile', 'no' );
		if ( 'yes' === $inline_mobile ) {
			$issues[] = __( 'Inline campaigns on mobile (content disruption)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 35;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 48;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 42;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of mobile optimization issues */
				__( 'OptinMonster has %d mobile optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/optinmonster-mobile-optimization',
		);
	}
}
