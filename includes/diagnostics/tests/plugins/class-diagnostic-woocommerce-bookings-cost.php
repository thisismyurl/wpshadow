<?php
/**
 * Woocommerce Bookings Cost Diagnostic
 *
 * Woocommerce Bookings Cost issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.648.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Bookings Cost Diagnostic Class
 *
 * @since 1.648.0000
 */
class Diagnostic_WoocommerceBookingsCost extends Diagnostic_Base {

	protected static $slug = 'woocommerce-bookings-cost';
	protected static $title = 'Woocommerce Bookings Cost';
	protected static $description = 'Woocommerce Bookings Cost issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		// Check if Bookings is active
		if ( ! class_exists( 'WC_Bookings' ) && ! defined( 'WC_BOOKINGS_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check for bookable products
		$bookable_products = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				 WHERE post_type = %s AND post_status = %s",
				'wc_booking',
				'publish'
			)
		);

		if ( $bookable_products > 0 ) {
			// Check cost calculation method
			$calculation_method = get_option( 'wc_bookings_cost_calculation_method', 'duration' );
			if ( $calculation_method === 'none' ) {
				$issues[] = 'cost_calculation_disabled';
				$threat_level += 30;
			}

			// Check person-based pricing
			$person_pricing = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->postmeta}
					 WHERE meta_key = %s
					 AND meta_value = %s",
					'_wc_booking_has_persons',
					'yes'
				)
			);
			if ( $person_pricing > 0 ) {
				$person_costs_enabled = get_option( 'wc_bookings_enable_person_costs', 'yes' );
				if ( $person_costs_enabled === 'no' ) {
					$issues[] = 'person_costs_disabled';
					$threat_level += 25;
				}
			}

			// Check resource costs
			$resources = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'bookable_resource'"
			);
			if ( $resources > 0 ) {
				$resource_costs = get_option( 'wc_bookings_enable_resource_costs', 'yes' );
				if ( $resource_costs === 'no' ) {
					$issues[] = 'resource_costs_disabled';
					$threat_level += 20;
				}
			}
		}

		// Check pricing rule validation
		$validate_pricing = get_option( 'wc_bookings_validate_pricing_rules', 'yes' );
		if ( $validate_pricing === 'no' ) {
			$issues[] = 'pricing_rule_validation_disabled';
			$threat_level += 20;
		}

		// Check for booking cost errors
		$cost_errors = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}
			 WHERE meta_key = '_booking_cost'
			 AND (meta_value = '0' OR meta_value = '')"
		);
		if ( $cost_errors > 10 ) {
			$issues[] = 'missing_booking_costs';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of cost calculation issues */
				__( 'WooCommerce Bookings cost calculation has problems: %s. This causes pricing errors and revenue loss.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-bookings-cost',
			);
		}
		
		return null;
	}
}
