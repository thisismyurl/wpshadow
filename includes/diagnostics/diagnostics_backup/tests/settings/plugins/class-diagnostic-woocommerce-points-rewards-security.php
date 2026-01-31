<?php
/**
 * Woocommerce Points Rewards Security Diagnostic
 *
 * Woocommerce Points Rewards Security issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.652.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Points Rewards Security Diagnostic Class
 *
 * @since 1.652.0000
 */
class Diagnostic_WoocommercePointsRewardsSecurity extends Diagnostic_Base {

	protected static $slug = 'woocommerce-points-rewards-security';
	protected static $title = 'Woocommerce Points Rewards Security';
	protected static $description = 'Woocommerce Points Rewards Security issues detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Points_Rewards' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify points expiration is configured
		$points_expire = get_option( 'wc_points_rewards_expire_points', 'no' );
		if ( 'no' === $points_expire ) {
			$issues[] = 'points_never_expire';
		}
		
		// Check 2: Verify points can't be transferred between users
		$allow_transfer = get_option( 'wc_points_rewards_allow_transfer', 'no' );
		if ( 'yes' === $allow_transfer ) {
			$issues[] = 'points_transfer_enabled';
		}
		
		// Check 3: Check for points balance manipulation
		global $wpdb;
		$points_table = $wpdb->prefix . 'wc_points_rewards_user_points';
		
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$points_table}'" ) === $points_table ) {
			// Check for unusually high point balances
			$suspicious_balances = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$points_table} 
					WHERE points_balance > %d",
					10000 // Adjust based on your business
				)
			);
			
			if ( $suspicious_balances > 0 ) {
				$issues[] = 'suspicious_high_point_balances';
			}
			
			// Check for rapid point accumulation (potential fraud)
			$points_log_table = $wpdb->prefix . 'wc_points_rewards_user_points_log';
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$points_log_table}'" ) === $points_log_table ) {
				$rapid_accumulation = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT user_id, COUNT(*) as trans_count 
						FROM {$points_log_table} 
						WHERE date > DATE_SUB(NOW(), INTERVAL %d HOUR)
						GROUP BY user_id
						HAVING trans_count > %d",
						24,
						50
					)
				);
				
				if ( ! empty( $rapid_accumulation ) ) {
					$issues[] = 'rapid_points_accumulation_detected';
				}
			}
		}
		
		// Check 4: Verify minimum order amount for points redemption
		$min_order_amount = get_option( 'wc_points_rewards_min_order_amount', 0 );
		if ( 0 === (float) $min_order_amount ) {
			$issues[] = 'no_minimum_order_for_points';
		}
		
		// Check 5: Verify points earning ratio is reasonable
		$earning_ratio = get_option( 'wc_points_rewards_earn_points_ratio', '1:1' );
		if ( empty( $earning_ratio ) ) {
			$issues[] = 'points_earning_ratio_not_configured';
		}
		
		// Check 6: Verify redemption ratio is configured
		$redemption_ratio = get_option( 'wc_points_rewards_redeem_points_ratio', '100:1' );
		if ( empty( $redemption_ratio ) ) {
			$issues[] = 'points_redemption_ratio_not_configured';
		}
		
		// Check 7: Verify cart abandonment doesn't award points
		$award_on_payment = get_option( 'wc_points_rewards_award_on_payment', 'yes' );
		if ( 'no' === $award_on_payment ) {
			$issues[] = 'points_awarded_before_payment';
		}
		
		if ( ! empty( $issues ) ) {
			$issues = array_unique( $issues );
			$description = sprintf(
				/* translators: %s: list of points & rewards security issues */
				__( 'WooCommerce Points & Rewards has security issues: %s. Insecure points configurations can lead to points fraud, revenue loss, and loyalty program abuse.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 70,
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/woocommerce-points-rewards-security',
			);
		}
		
		return null;
	}
}
