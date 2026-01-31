<?php
/**
 * Booking Refund Processing Diagnostic
 *
 * Booking refunds not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.627.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Refund Processing Diagnostic Class
 *
 * @since 1.627.0000
 */
class Diagnostic_BookingRefundProcessing extends Diagnostic_Base {

	protected static $slug = 'booking-refund-processing';
	protected static $title = 'Booking Refund Processing';
	protected static $description = 'Booking refunds not validated';
	protected static $family = 'security';

	public static function check() {
		// Check for booking plugins
		$has_booking = class_exists( 'WooCommerce_Bookings' ) ||
		               defined( 'BOOKLY_VERSION' ) ||
		               class_exists( 'Amelia' ) ||
		               function_exists( 'wc_bookings_get_booking' );
		
		if ( ! $has_booking ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Refund policy configured
		$refund_policy = get_option( 'booking_refund_policy', '' );
		if ( empty( $refund_policy ) ) {
			$issues[] = __( 'No refund policy configured (legal requirement)', 'wpshadow' );
		}
		
		// Check 2: Admin approval required
		$auto_refund = get_option( 'booking_auto_refund', 'no' );
		if ( 'yes' === $auto_refund ) {
			$issues[] = __( 'Auto-refunds enabled (fraud risk)', 'wpshadow' );
		}
		
		// Check 3: Payment gateway validation
		$validate_gateway = get_option( 'booking_validate_gateway_refund', 'yes' );
		if ( 'no' === $validate_gateway ) {
			$issues[] = __( 'Gateway refund validation disabled (double refund risk)', 'wpshadow' );
		}
		
		// Check 4: Refund time limits
		$refund_deadline = get_option( 'booking_refund_deadline', 0 );
		if ( $refund_deadline === 0 ) {
			$issues[] = __( 'No refund deadline (indefinite liability)', 'wpshadow' );
		}
		
		// Check 5: Refund logging
		$log_refunds = get_option( 'booking_log_refunds', 'yes' );
		if ( 'no' === $log_refunds ) {
			$issues[] = __( 'Refund logging disabled (no audit trail)', 'wpshadow' );
		}
		
		// Check 6: Partial refund support
		$partial_refunds = get_option( 'booking_partial_refunds', 'yes' );
		if ( 'no' === $partial_refunds ) {
			$issues[] = __( 'Partial refunds disabled (all-or-nothing)', 'wpshadow' );
		}
		
		// Check 7: Fraud detection
		$fraud_check = get_option( 'booking_refund_fraud_check', 'no' );
		if ( 'no' === $fraud_check ) {
			$issues[] = __( 'No fraud detection for refunds (abuse risk)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 82;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 76;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of refund processing issues */
				__( 'Booking refund processing has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/booking-refund-processing',
		);
	}
}
