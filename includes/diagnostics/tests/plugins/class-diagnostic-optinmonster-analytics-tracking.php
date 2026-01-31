<?php
/**
 * OptinMonster Analytics Tracking Diagnostic
 *
 * OptinMonster analytics not tracking conversions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.222.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OptinMonster Analytics Tracking Diagnostic Class
 *
 * @since 1.222.0000
 */
class Diagnostic_OptinmonsterAnalyticsTracking extends Diagnostic_Base {

	protected static $slug = 'optinmonster-analytics-tracking';
	protected static $title = 'OptinMonster Analytics Tracking';
	protected static $description = 'OptinMonster analytics not tracking conversions';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'OMAPI_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API connection
		$api_key = get_option( 'optin_monster_api_key', '' );
		if ( empty( $api_key ) ) {
			return null;
		}
		
		// Check 2: Conversion tracking
		$conversion_tracking = get_option( 'optinmonster_conversion_tracking', 'yes' );
		if ( 'no' === $conversion_tracking ) {
			$issues[] = __( 'Conversion tracking disabled (no ROI data)', 'wpshadow' );
		}
		
		// Check 3: Analytics integration
		$ga_integration = get_option( 'optinmonster_ga_integration', 'no' );
		if ( 'no' === $ga_integration && function_exists( 'gtag' ) ) {
			$issues[] = __( 'Google Analytics detected but not integrated', 'wpshadow' );
		}
		
		// Check 4: Campaign count
		$campaigns = get_option( 'optinmonster_campaigns', array() );
		if ( empty( $campaigns ) ) {
			$issues[] = __( 'No campaigns configured (plugin not utilized)', 'wpshadow' );
		}
		
		// Check 5: A/B testing
		$ab_testing = 0;
		foreach ( $campaigns as $campaign ) {
			if ( isset( $campaign['ab_testing'] ) && $campaign['ab_testing'] ) {
				$ab_testing++;
			}
		}
		
		if ( $ab_testing === 0 && count( $campaigns ) > 0 ) {
			$issues[] = __( 'No A/B tests running (missing optimization)', 'wpshadow' );
		}
		
		// Check 6: Success event tracking
		$event_tracking = get_option( 'optinmonster_event_tracking', 'no' );
		if ( 'no' === $event_tracking ) {
			$issues[] = __( 'Success event tracking disabled (incomplete analytics)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 40;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 50;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 45;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of analytics tracking issues */
				__( 'OptinMonster analytics has %d tracking issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/optinmonster-analytics-tracking',
		);
	}
}
