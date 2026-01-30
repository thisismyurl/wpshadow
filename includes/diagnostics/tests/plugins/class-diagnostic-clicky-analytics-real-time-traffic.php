<?php
/**
 * Clicky Analytics Real Time Traffic Diagnostic
 *
 * Clicky Analytics Real Time Traffic misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1357.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clicky Analytics Real Time Traffic Diagnostic Class
 *
 * @since 1.1357.0000
 */
class Diagnostic_ClickyAnalyticsRealTimeTraffic extends Diagnostic_Base {

	protected static $slug = 'clicky-analytics-real-time-traffic';
	protected static $title = 'Clicky Analytics Real Time Traffic';
	protected static $description = 'Clicky Analytics Real Time Traffic misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Clicky Analytics
		$has_clicky = defined( 'CLICKY_VERSION' ) ||
		              get_option( 'clicky_site_id', '' ) ||
		              function_exists( 'clicky_analytics' );
		
		if ( ! $has_clicky ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Site ID configured
		$site_id = get_option( 'clicky_site_id', '' );
		if ( empty( $site_id ) ) {
			$issues[] = __( 'Clicky Site ID not configured (not tracking)', 'wpshadow' );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Clicky Analytics not connected', 'wpshadow' ),
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/clicky-analytics-real-time-traffic',
			);
		}
		
		// Check 2: Admin tracking
		$track_admin = get_option( 'clicky_track_admin', true );
		if ( $track_admin ) {
			$issues[] = __( 'Tracking admin users (skewed analytics)', 'wpshadow' );
		}
		
		// Check 3: Outbound link tracking
		$track_outbound = get_option( 'clicky_track_outbound', false );
		if ( ! $track_outbound ) {
			$issues[] = __( 'Outbound link tracking disabled (incomplete data)', 'wpshadow' );
		}
		
		// Check 4: Async loading
		$async_load = get_option( 'clicky_async', true );
		if ( ! $async_load ) {
			$issues[] = __( 'Synchronous tracking (page load delay)', 'wpshadow' );
		}
		
		// Check 5: Cookie-less tracking
		$cookieless = get_option( 'clicky_cookieless', false );
		if ( ! $cookieless ) {
			$issues[] = __( 'Using cookies (GDPR consideration)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of configuration issues */
				__( 'Clicky Analytics has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/clicky-analytics-real-time-traffic',
		);
	}
}
