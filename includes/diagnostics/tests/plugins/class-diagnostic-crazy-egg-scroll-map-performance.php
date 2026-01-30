<?php
/**
 * Crazy Egg Scroll Map Performance Diagnostic
 *
 * Crazy Egg Scroll Map Performance misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1375.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Crazy Egg Scroll Map Performance Diagnostic Class
 *
 * @since 1.1375.0000
 */
class Diagnostic_CrazyEggScrollMapPerformance extends Diagnostic_Base {

	protected static $slug = 'crazy-egg-scroll-map-performance';
	protected static $title = 'Crazy Egg Scroll Map Performance';
	protected static $description = 'Crazy Egg Scroll Map Performance misconfigured';
	protected static $family = 'performance';

	public static function check() {
		// Check for Crazy Egg
		$has_crazyegg = get_option( 'crazyegg_account_id', '' ) ||
		                defined( 'CRAZYEGG_ACCOUNT' ) ||
		                function_exists( 'crazyegg_tracking_code' );
		
		if ( ! $has_crazyegg ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Account ID configured
		$account_id = get_option( 'crazyegg_account_id', '' );
		if ( empty( $account_id ) ) {
			return null;
		}
		
		// Check 2: Async loading
		$async_load = get_option( 'crazyegg_async', true );
		if ( ! $async_load ) {
			$issues[] = __( 'Synchronous loading (page load delay)', 'wpshadow' );
		}
		
		// Check 3: Sampling rate
		$sampling_rate = get_option( 'crazyegg_sampling_rate', 100 );
		if ( $sampling_rate === 100 ) {
			$issues[] = __( 'Recording 100% of sessions (unnecessary overhead)', 'wpshadow' );
		}
		
		// Check 4: Mobile recording
		$mobile_recording = get_option( 'crazyegg_record_mobile', true );
		if ( $mobile_recording ) {
			$issues[] = __( 'Recording mobile sessions (performance impact)', 'wpshadow' );
		}
		
		// Check 5: Session limit
		$session_limit = get_option( 'crazyegg_session_limit', 0 );
		if ( $session_limit === 0 ) {
			$issues[] = __( 'No session recording limit (quota overrun)', 'wpshadow' );
		}
		
		// Check 6: GDPR compliance
		$gdpr_mode = get_option( 'crazyegg_gdpr_mode', false );
		if ( ! $gdpr_mode ) {
			$issues[] = __( 'GDPR mode disabled (privacy concern)', 'wpshadow' );
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
				/* translators: %s: list of Crazy Egg performance issues */
				__( 'Crazy Egg has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/crazy-egg-scroll-map-performance',
		);
	}
}
