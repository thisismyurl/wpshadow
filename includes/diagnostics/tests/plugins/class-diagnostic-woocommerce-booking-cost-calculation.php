<?php
/**
 * WooCommerce Booking Cost Calculation Diagnostic
 *
 * WooCommerce booking costs calculable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.616.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Booking Cost Calculation Diagnostic Class
 *
 * @since 1.616.0000
 */
class Diagnostic_WoocommerceBookingCostCalculation extends Diagnostic_Base {

	protected static $slug = 'woocommerce-booking-cost-calculation';
	protected static $title = 'WooCommerce Booking Cost Calculation';
	protected static $description = 'WooCommerce booking costs calculable';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Bookings' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Bookable products exist
		$bookable_products = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p
				 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				 WHERE p.post_type = %s AND pm.meta_key = %s AND pm.meta_value = %s",
				'product',
				'_wc_booking_type',
				'booking'
			)
		);
		
		if ( $bookable_products === 0 ) {
			return null;
		}
		
		// Check 2: Cost rules validation
		$invalid_cost_rules = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta}
				 WHERE meta_key = %s AND (meta_value = '' OR meta_value IS NULL OR meta_value = '0')",
				'_wc_booking_cost'
			)
		);
		
		if ( $invalid_cost_rules > 0 ) {
			$issues[] = sprintf( __( '%d bookable products with missing base cost', 'wpshadow' ), $invalid_cost_rules );
		}
		
		// Check 3: Person-based pricing validation
		$person_pricing = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta}
				 WHERE meta_key = %s AND meta_value = %s",
				'_wc_booking_has_persons',
				'yes'
			)
		);
		
		foreach ( $person_pricing as $product ) {
			$person_cost = get_post_meta( $product->post_id, '_wc_booking_person_cost', true );
			if ( empty( $person_cost ) ) {
				$issues[] = sprintf( __( 'Product ID %d has person pricing without cost defined', 'wpshadow' ), $product->post_id );
				break;
			}
		}
		
		// Check 4: Resource pricing
		$resource_pricing = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}wc_booking_relationships WHERE resource_id > 0"
		);
		
		if ( $resource_pricing > 0 ) {
			$missing_resource_costs = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->prefix}wc_booking_relationships r
				 LEFT JOIN {$wpdb->postmeta} pm ON r.resource_id = pm.post_id AND pm.meta_key = '_wc_booking_resource_base_cost'
				 WHERE r.resource_id > 0 AND (pm.meta_value IS NULL OR pm.meta_value = '')"
			);
			
			if ( $missing_resource_costs > 0 ) {
				$issues[] = sprintf( __( '%d resources without pricing configured', 'wpshadow' ), $missing_resource_costs );
			}
		}
		
		// Check 5: Duration-based pricing
		$duration_pricing = get_option( 'wc_bookings_duration_pricing_enabled', false );
		if ( $duration_pricing ) {
			$duration_rules = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
					'_wc_booking_pricing'
				)
			);
			
			if ( $duration_rules === 0 ) {
				$issues[] = __( 'Duration pricing enabled but no rules configured', 'wpshadow' );
			}
		}
		
		// Check 6: Cost calculation error logs
		$error_log = get_option( 'wc_bookings_cost_errors', array() );
		if ( is_array( $error_log ) && count( $error_log ) > 5 ) {
			$issues[] = sprintf( __( '%d cost calculation errors logged', 'wpshadow' ), count( $error_log ) );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 65;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 78;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 72;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of calculation issues */
				__( 'WooCommerce Bookings cost calculation has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/woocommerce-booking-cost-calculation',
		);
	}
}
