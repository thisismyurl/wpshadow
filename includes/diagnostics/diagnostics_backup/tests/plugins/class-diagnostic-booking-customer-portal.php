<?php
/**
 * Booking Customer Portal Diagnostic
 *
 * Booking portal permissions wrong.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.625.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Customer Portal Diagnostic Class
 *
 * @since 1.625.0000
 */
class Diagnostic_BookingCustomerPortal extends Diagnostic_Base {

	protected static $slug = 'booking-customer-portal';
	protected static $title = 'Booking Customer Portal';
	protected static $description = 'Booking portal permissions wrong';
	protected static $family = 'security';

	public static function check() {
		// Check for booking plugins with customer portals
		$has_booking = class_exists( 'WooCommerce_Bookings' ) ||
		               function_exists( 'wc_bookings_get_booking' ) ||
		               class_exists( 'EM_Booking' );

		if ( ! $has_booking ) {
			return null;
		}

		$issues = array();

		// Check 1: Login required
		$require_login = get_option( 'booking_portal_require_login', 'no' );
		if ( 'no' === $require_login ) {
			$issues[] = __( 'Login not required (public access)', 'wpshadow' );
		}

		// Check 2: Customer data visibility
		$data_visibility = get_option( 'booking_portal_data_visibility', 'all' );
		if ( 'all' === $data_visibility ) {
			$issues[] = __( 'All customer data visible (privacy risk)', 'wpshadow' );
		}

		// Check 3: Booking modification
		$allow_modify = get_option( 'booking_portal_allow_modify', 'yes' );
		if ( 'yes' === $allow_modify ) {
			$issues[] = __( 'Customers can modify bookings (conflict risk)', 'wpshadow' );
		}

		// Check 4: Cancellation policy
		$cancellation = get_option( 'booking_portal_cancellation', 'anytime' );
		if ( 'anytime' === $cancellation ) {
			$issues[] = __( 'Cancel anytime (revenue loss)', 'wpshadow' );
		}

		// Check 5: Payment information
		$show_payment = get_option( 'booking_portal_show_payment', 'yes' );
		if ( 'yes' === $show_payment ) {
			$issues[] = __( 'Payment info visible (PCI concern)', 'wpshadow' );
		}

		// Check 6: Session timeout
		$session_timeout = get_option( 'booking_portal_session_timeout', 0 );
		if ( $session_timeout === 0 ) {
			$issues[] = __( 'No session timeout (security risk)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 65;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 77;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 71;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Booking customer portal has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/booking-customer-portal',
		);
	}
}
