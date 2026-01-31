<?php
/**
 * Appointment Booking Security Diagnostic
 *
 * Appointment booking data insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.603.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Appointment Booking Security Diagnostic Class
 *
 * @since 1.603.0000
 */
class Diagnostic_AppointmentBookingSecurity extends Diagnostic_Base {

	protected static $slug = 'appointment-booking-security';
	protected static $title = 'Appointment Booking Security';
	protected static $description = 'Appointment booking data insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'EDD_Bookings' ) && ! defined( 'WAPPOINTMENT_VERSION' ) && ! class_exists( 'WPBS_Init' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Customer data encryption.
		$encrypt_data = get_option( 'appointment_encrypt_customer_data', '0' );
		if ( '0' === $encrypt_data ) {
			$issues[] = 'customer data not encrypted in database';
		}

		// Check 2: Access controls.
		$customer_access = get_option( 'appointment_customer_can_view_own_only', '1' );
		if ( '0' === $customer_access ) {
			$issues[] = 'customers can view all bookings (privacy breach)';
		}

		// Check 3: Admin restrictions.
		$admin_access = get_option( 'appointment_restrict_admin_data', '0' );
		if ( '0' === $admin_access ) {
			$issues[] = 'no restrictions on admin data access';
		}

		// Check 4: GDPR compliance.
		$gdpr_enabled = get_option( 'appointment_gdpr_compliant', '0' );
		if ( '0' === $gdpr_enabled ) {
			$issues[] = 'GDPR compliance features not enabled';
		}

		// Check 5: Data retention policy.
		$retention_days = get_option( 'appointment_data_retention_days', 0 );
		if ( 0 === $retention_days ) {
			$issues[] = 'no data retention policy (data stored indefinitely)';
		}

		// Check 6: Booking confirmation code.
		$use_confirmation = get_option( 'appointment_use_confirmation_code', '1' );
		if ( '0' === $use_confirmation ) {
			$issues[] = 'booking confirmation codes not used (accessibility/security issue)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 90, 70 + ( count( $issues ) * 4 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Appointment booking security issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/appointment-booking-security',
			);
		}

		return null;
	}
}
