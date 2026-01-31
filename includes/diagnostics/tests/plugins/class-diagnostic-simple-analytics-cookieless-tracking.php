<?php
/**
 * Simple Analytics Cookieless Tracking Diagnostic
 *
 * Simple Analytics Cookieless Tracking misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1368.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Simple Analytics Cookieless Tracking Diagnostic Class
 *
 * @since 1.1368.0000
 */
class Diagnostic_SimpleAnalyticsCookielessTracking extends Diagnostic_Base {

	protected static $slug = 'simple-analytics-cookieless-tracking';
	protected static $title = 'Simple Analytics Cookieless Tracking';
	protected static $description = 'Simple Analytics Cookieless Tracking misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		
		$issues = array();

		// Check 1: Verify cookieless mode is enabled
		$cookieless_mode = get_option( 'simple_analytics_cookieless_mode', false );
		if ( ! $cookieless_mode ) {
			$issues[] = __( 'Cookieless tracking mode not enabled', 'wpshadow' );
		}

		// Check 2: Check data collection methods
		$collection_method = get_option( 'simple_analytics_collection_method', '' );
		if ( 'server_side' !== $collection_method ) {
			$issues[] = __( 'Server-side data collection not configured', 'wpshadow' );
		}

		// Check 3: Verify privacy compliance settings
		$privacy_compliant = get_option( 'simple_analytics_privacy_compliant', false );
		if ( ! $privacy_compliant ) {
			$issues[] = __( 'Privacy compliance settings not configured', 'wpshadow' );
		}

		// Check 4: Check tracking script configuration
		$script_config = get_option( 'simple_analytics_script_config', array() );
		if ( empty( $script_config ) ) {
			$issues[] = __( 'Tracking script not properly configured', 'wpshadow' );
		}

		// Check 5: Verify data retention policy
		$data_retention = get_option( 'simple_analytics_data_retention_days', 0 );
		if ( $data_retention === 0 || $data_retention > 365 ) {
			$issues[] = __( 'Data retention period not configured', 'wpshadow' );
		}

		// Check 6: Check server-side tracking configuration
		$server_tracking = get_option( 'simple_analytics_server_tracking', false );
		if ( ! $server_tracking ) {
			$issues[] = __( 'Server-side tracking not enabled', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Simple Analytics cookieless tracking issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/simple-analytics-cookieless-tracking',
			);
		}

		return null;
	}
}
