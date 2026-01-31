<?php
/**
 * Clicky Analytics Goal Tracking Diagnostic
 *
 * Clicky Analytics Goal Tracking misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1358.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clicky Analytics Goal Tracking Diagnostic Class
 *
 * @since 1.1358.0000
 */
class Diagnostic_ClickyAnalyticsGoalTracking extends Diagnostic_Base {

	protected static $slug = 'clicky-analytics-goal-tracking';
	protected static $title = 'Clicky Analytics Goal Tracking';
	protected static $description = 'Clicky Analytics Goal Tracking misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Clicky Analytics
		$has_clicky = defined( 'CLICKY_VERSION' ) ||
		              get_option( 'clicky_site_id', '' ) ||
		              function_exists( 'clicky_analytics' );
		
		if ( ! $has_clicky ) {
			return null;
		}
		
		$site_id = get_option( 'clicky_site_id', '' );
		if ( empty( $site_id ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Goals configured
		$goals = get_option( 'clicky_goals', array() );
		if ( empty( $goals ) || count( $goals ) === 0 ) {
			$issues[] = __( 'No goals configured (no conversion tracking)', 'wpshadow' );
		}
		
		// Check 2: Goal value tracking
		$track_values = get_option( 'clicky_track_goal_values', false );
		if ( ! $track_values && ! empty( $goals ) ) {
			$issues[] = __( 'Goal values not tracked (ROI unknown)', 'wpshadow' );
		}
		
		// Check 3: E-commerce tracking
		if ( class_exists( 'WooCommerce' ) ) {
			$ecommerce_tracking = get_option( 'clicky_ecommerce', false );
			if ( ! $ecommerce_tracking ) {
				$issues[] = __( 'WooCommerce detected but e-commerce tracking disabled', 'wpshadow' );
			}
		}
		
		// Check 4: Funnel tracking
		$funnel_tracking = get_option( 'clicky_funnel_tracking', false );
		if ( ! $funnel_tracking ) {
			$issues[] = __( 'Funnel tracking disabled (no drop-off analysis)', 'wpshadow' );
		}
		
		// Check 5: Goal validation
		foreach ( $goals as $goal ) {
			if ( ! isset( $goal['name'] ) || empty( $goal['name'] ) ) {
				$issues[] = __( 'Unnamed goal configured (tracking confusion)', 'wpshadow' );
				break;
			}
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
				/* translators: %s: list of goal tracking issues */
				__( 'Clicky goal tracking has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/clicky-analytics-goal-tracking',
		);
	}
}
