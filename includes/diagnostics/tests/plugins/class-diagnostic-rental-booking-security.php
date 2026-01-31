<?php
/**
 * Rental Booking Security Diagnostic
 *
 * Rental bookings not secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.611.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rental Booking Security Diagnostic Class
 *
 * @since 1.611.0000
 */
class Diagnostic_RentalBookingSecurity extends Diagnostic_Base {

	protected static $slug = 'rental-booking-security';
	protected static $title = 'Rental Booking Security';
	protected static $description = 'Rental bookings not secured';
	protected static $family = 'security';

	public static function check() {
		global $wpdb;
		
		// Check for rental booking post types
		$rental_bookings = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN (%s, %s, %s) AND post_status = %s",
				'rental',
				'booking',
				'rental_booking',
				'publish'
			)
		);
		
		if ( $rental_bookings === 0 ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Booking access control
		$restrict_bookings = get_option( 'rental_restrict_booking_access', false );
		if ( ! $restrict_bookings ) {
			$issues[] = __( 'Booking access not restricted to logged-in users', 'wpshadow' );
		}
		
		// Check 2: Double booking prevention
		$double_booking_check = get_option( 'rental_prevent_double_booking', false );
		if ( ! $double_booking_check ) {
			$issues[] = __( 'Double booking prevention not enabled', 'wpshadow' );
		}
		
		// Check 3: Nonce verification on booking forms
		$nonce_enabled = get_option( 'rental_booking_nonce_enabled', false );
		if ( ! $nonce_enabled ) {
			$issues[] = __( 'CSRF protection (nonce) not enabled on booking forms', 'wpshadow' );
		}
		
		// Check 4: Input sanitization
		$sanitize_inputs = get_option( 'rental_sanitize_booking_data', false );
		if ( ! $sanitize_inputs ) {
			$issues[] = __( 'Booking input sanitization not configured', 'wpshadow' );
		}
		
		// Check 5: Check for booking data in postmeta
		$booking_meta = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
				'%booking_%'
			)
		);
		
		if ( $booking_meta > 0 ) {
			// Check if sensitive data is encrypted
			$encryption_enabled = get_option( 'rental_encrypt_booking_data', false );
			if ( ! $encryption_enabled ) {
				$issues[] = sprintf( __( '%d booking records without encryption', 'wpshadow' ), $booking_meta );
			}
		}
		
		// Check 6: Session management
		$session_timeout = get_option( 'rental_booking_session_timeout', 0 );
		if ( $session_timeout === 0 || $session_timeout > 3600 ) {
			$issues[] = __( 'Booking session timeout too long or not configured', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 85;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 78;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of security issues */
				__( 'Rental booking system has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/rental-booking-security',
		);
	}
}
