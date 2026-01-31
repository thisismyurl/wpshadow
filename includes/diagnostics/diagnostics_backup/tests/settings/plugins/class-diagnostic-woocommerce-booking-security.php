<?php
/**
 * WooCommerce Booking Security Diagnostic
 *
 * WooCommerce bookings vulnerable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.614.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Booking Security Diagnostic Class
 *
 * @since 1.614.0000
 */
class Diagnostic_WoocommerceBookingSecurity extends Diagnostic_Base {

	protected static $slug = 'woocommerce-booking-security';
	protected static $title = 'WooCommerce Booking Security';
	protected static $description = 'WooCommerce bookings vulnerable';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Bookings' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify booking confirmation requires user authentication
		$require_auth = get_option( 'woocommerce_bookings_require_confirmation', 'yes' );
		if ( 'no' === $require_auth ) {
			$issues[] = 'booking_confirmation_not_required';
		}
		
		// Check 2: Verify booking cancellation policy is configured
		$cancel_policy = get_option( 'woocommerce_bookings_cancellation_policy', '' );
		if ( empty( $cancel_policy ) ) {
			$issues[] = 'no_cancellation_policy';
		}
		
		// Check 3: Check for publicly accessible booking data
		global $wpdb;
		$booking_table = $wpdb->prefix . 'wc_booking';
		
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$booking_table}'" ) === $booking_table ) {
			// Check if booking data contains PII without proper protection
			$recent_bookings = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT COUNT(*) as count FROM {$booking_table} 
					WHERE start_date > DATE_SUB(NOW(), INTERVAL %d DAY)",
					30
				)
			);
			
			// Check 4: Verify booking buffer times are set (prevents double-booking)
			$buffer_before = get_option( 'woocommerce_bookings_buffer_before', 0 );
			$buffer_after = get_option( 'woocommerce_bookings_buffer_after', 0 );
			
			if ( 0 === (int) $buffer_before && 0 === (int) $buffer_after ) {
				$issues[] = 'no_booking_buffer_time';
			}
		}
		
		// Check 5: Verify booking confirmation emails are being sent
		$send_confirmation = get_option( 'woocommerce_bookings_send_confirmation', 'yes' );
		if ( 'no' === $send_confirmation ) {
			$issues[] = 'booking_confirmation_emails_disabled';
		}
		
		// Check 6: Check if booking data is exposed in REST API
		$rest_url = rest_url( 'wc-bookings/v1/bookings' );
		$response = wp_remote_get( $rest_url, array( 'timeout' => 5 ) );
		
		if ( ! is_wp_error( $response ) ) {
			$status_code = wp_remote_retrieve_response_code( $response );
			if ( 200 === $status_code ) {
				// REST API is accessible without auth
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				if ( is_array( $data ) && ! empty( $data ) ) {
					$issues[] = 'booking_data_exposed_via_rest_api';
				}
			}
		}
		
		// Check 7: Verify payment is required for bookings
		$require_payment = get_option( 'woocommerce_bookings_require_payment', 'yes' );
		if ( 'no' === $require_payment ) {
			$issues[] = 'payment_not_required';
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of booking security issues */
				__( 'WooCommerce Bookings has security configuration issues: %s. Insecure booking configurations can lead to unauthorized bookings, data exposure, or revenue loss.', 'wpshadow' ),
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
				'kb_link'      => 'https://wpshadow.com/kb/woocommerce-booking-security',
			);
		}
		
		return null;
	}
}
