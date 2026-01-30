<?php
/**
 * Woocommerce Points Rewards Expiry Diagnostic
 *
 * Woocommerce Points Rewards Expiry issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.651.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Points Rewards Expiry Diagnostic Class
 *
 * @since 1.651.0000
 */
class Diagnostic_WoocommercePointsRewardsExpiry extends Diagnostic_Base {

	protected static $slug = 'woocommerce-points-rewards-expiry';
	protected static $title = 'Woocommerce Points Rewards Expiry';
	protected static $description = 'Woocommerce Points Rewards Expiry issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) && ! class_exists( 'WC_Points_Rewards' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Points expiry enabled
		$expiry_enabled = get_option( 'wc_points_rewards_points_expiry', 0 );
		if ( ! $expiry_enabled ) {
			$issues[] = 'Points expiry not enabled';
		}
		
		// Check 2: Expiry days configured
		$expiry_days = absint( get_option( 'wc_points_rewards_points_expiry_days', 0 ) );
		if ( $expiry_days <= 0 ) {
			$issues[] = 'Points expiry days not configured';
		}
		
		// Check 3: Expiry notification enabled
		$expiry_notice = get_option( 'wc_points_rewards_expiry_notice', 0 );
		if ( ! $expiry_notice ) {
			$issues[] = 'Expiry notification not enabled';
		}
		
		// Check 4: Redemption ratio configured
		$redeem_ratio = get_option( 'wc_points_rewards_redeem_ratio', '' );
		if ( empty( $redeem_ratio ) ) {
			$issues[] = 'Redemption ratio not configured';
		}
		
		// Check 5: Earn points configuration
		$earn_ratio = get_option( 'wc_points_rewards_earn_ratio', '' );
		if ( empty( $earn_ratio ) ) {
			$issues[] = 'Points earning ratio not configured';
		}
		
		// Check 6: Expiry grace period
		$grace_period = absint( get_option( 'wc_points_rewards_expiry_grace_period', 0 ) );
		if ( $grace_period <= 0 ) {
			$issues[] = 'Expiry grace period not configured';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d points expiry configuration issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-points-rewards-expiry',
			);
		}
		
		return null;
	}
}
