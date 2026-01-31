<?php
/**
 * Amelia Booking Security Diagnostic
 *
 * Amelia booking data exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.464.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Amelia Booking Security Diagnostic Class
 *
 * @since 1.464.0000
 */
class Diagnostic_AmeliaBookingSecurity extends Diagnostic_Base {

	protected static $slug = 'amelia-booking-security';
	protected static $title = 'Amelia Booking Security';
	protected static $description = 'Amelia booking data exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AMELIA_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Customer data encryption.
		$encrypt_data = get_option( 'amelia_settings_booking_encryptCustomerData', false );
		if ( ! $encrypt_data ) {
			$issues[] = 'customer booking data not encrypted';
		}

		// Check 2: Payment gateway security.
		$payment_gateway = get_option( 'amelia_settings_payments_gateway', '' );
		if ( empty( $payment_gateway ) ) {
			$issues[] = 'no payment gateway configured';
		}

		// Check 3: SSL requirement for booking.
		if ( ! is_ssl() ) {
			$issues[] = 'bookings not using HTTPS (data transmitted insecurely)';
		}

		// Check 4: GDPR compliance.
		$gdpr_enabled = get_option( 'amelia_settings_gdpr_enabled', false );
		if ( ! $gdpr_enabled ) {
			$issues[] = 'GDPR compliance not enabled';
		}

		// Check 5: Access control permissions.
		global $wpdb;
		$booking_table = $wpdb->prefix . 'amelia_bookings';
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $booking_table ) );
		if ( $table_exists && ! function_exists( 'amelia_verify_booking_access' ) ) {
			$issues[] = 'no custom access control for booking data';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 90, 65 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Booking security issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/amelia-booking-security',
			);
		}

		return null;
	}
}
