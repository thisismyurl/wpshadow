<?php
/**
 * Woocommerce Bookings Buffer Diagnostic
 *
 * Woocommerce Bookings Buffer issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.647.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Bookings Buffer Diagnostic Class
 *
 * @since 1.647.0000
 */
class Diagnostic_WoocommerceBookingsBuffer extends Diagnostic_Base {

	protected static $slug = 'woocommerce-bookings-buffer';
	protected static $title = 'Woocommerce Bookings Buffer';
	protected static $description = 'Woocommerce Bookings Buffer issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Bookings' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Bookable products exist
		$bookable_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p
				 INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
				 INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				 INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
				 WHERE p.post_type = %s AND tt.taxonomy = %s AND t.slug = %s",
				'product',
				'product_type',
				'booking'
			)
		);
		
		if ( $bookable_count === 0 ) {
			return null;
		}
		
		// Check 2: Global buffer time
		$buffer_before = get_option( 'woocommerce_bookings_buffer_before', 0 );
		$buffer_after = get_option( 'woocommerce_bookings_buffer_after', 0 );
		
		if ( $buffer_before === 0 && $buffer_after === 0 ) {
			$issues[] = __( 'No buffer time configured (back-to-back bookings)', 'wpshadow' );
		}
		
		// Check 3: Minimum notice period
		$min_notice = get_option( 'woocommerce_bookings_minimum_notice', 0 );
		if ( $min_notice < 1 ) {
			$issues[] = __( 'No minimum notice period (same-minute booking possible)', 'wpshadow' );
		}
		
		// Check 4: Maximum notice period
		$max_notice = get_option( 'woocommerce_bookings_maximum_notice', 0 );
		if ( $max_notice === 0 ) {
			$issues[] = __( 'Unlimited advance booking (calendar clutter)', 'wpshadow' );
		}
		
		// Check 5: Check-in/checkout times
		$check_in_time = get_option( 'woocommerce_bookings_check_in_time', '' );
		if ( empty( $check_in_time ) ) {
			$issues[] = __( 'Check-in time not set (scheduling confusion)', 'wpshadow' );
		}
		
		// Check 6: Timezone settings
		$use_customer_tz = get_option( 'woocommerce_bookings_use_customer_timezone', false );
		if ( $use_customer_tz ) {
			$issues[] = __( 'Customer timezone enabled (potential conflicts)', 'wpshadow' );
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
				/* translators: %s: list of booking buffer issues */
				__( 'WooCommerce Bookings buffer has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/woocommerce-bookings-buffer',
		);
	}
}
